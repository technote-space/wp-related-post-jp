<?php
/**
 * Technote Traits Presenter
 *
 * @version 1.1.41
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Technote\Traits;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Trait Presenter
 * @package Technote\Traits
 * @property \Technote $app
 */
trait Presenter {

	/** @var array $_prev_post */
	private $_prev_post = null;

	/**
	 * @param string $name
	 * @param array $args
	 * @param bool $echo
	 * @param bool $error
	 *
	 * @return string
	 */
	public function get_view( $name, $args = [], $echo = false, $error = true ) {
		$name = trim( $name, '/' . DS );
		$name = str_replace( '/', DS, $name );
		$name .= '.php';
		$path = null;
		if ( is_readable( $this->app->define->plugin_views_dir . DS . $name ) ) {
			$path = $this->app->define->plugin_views_dir . DS . $name;
		} elseif ( is_readable( $this->app->define->lib_views_dir . DS . $name ) ) {
			$path = $this->app->define->lib_views_dir . DS . $name;
		}

		$view = '';
		if ( isset( $path ) ) {
			unset( $name );
			$args = $this->get_presenter_args( $args );
			extract( $args, EXTR_SKIP );

			ob_start();
			/** @noinspection PhpIncludeInspection */
			@include $path;
			$view = ob_get_contents();
			ob_end_clean();
		} elseif ( $error ) {
			$this->app->log( sprintf( 'View file [ %s ] not found.', $name ) );
		}

		if ( $echo ) {
			echo $view;
		}

		return $view;
	}

	/**
	 * @return string
	 */
	private function get_api_class() {
		return $this->get_slug( 'api_class', '_rest_api' );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function get_presenter_args( $args ) {
		$args['field'] = array_merge( \Technote\Models\Utility::array_get( $args, 'field', [] ), $this->app->input->all() );
		if ( $this instanceof \Technote\Interfaces\Nonce ) {
			$args['nonce_key']   = $this->get_nonce_key();
			$args['nonce_value'] = $this->create_nonce();
		}
		$args['instance']  = $this;
		$args['action']    = $this->app->input->server( "REQUEST_URI" );
		$args['is_admin']  = is_admin();
		$args['user_can']  = $this->app->user_can();
		$args['api_class'] = $this->get_api_class();

		return $this->filter_presenter_args( $args );
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	protected function filter_presenter_args( $args ) {
		return $args;
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param array $overwrite
	 * @param bool $echo
	 * @param bool $error
	 *
	 * @return string
	 */
	public function form( $name, $args = [], $overwrite = [], $echo = true, $error = true ) {
		return $this->get_view( 'include/form/' . trim( $name, '/' . DS ), array_merge( $args, $overwrite ), $echo, $error );
	}

	/**
	 * @param string $name
	 * @param mixed $data
	 * @param string|null $key
	 * @param string $default
	 * @param bool $checkbox
	 *
	 * @return mixed
	 */
	public function old( $name, $data, $key = null, $default = '', $checkbox = false ) {
		if ( is_array( $data ) ) {
			$default = \Technote\Models\Utility::array_get( $data, $key, $default );
		} elseif ( $data instanceof \stdClass ) {
			$default = property_exists( $data, $key ) ? $data->$key : $default;
		} elseif ( ! isset( $key ) ) {
			$default = $data;
		}
		if ( ! isset( $this->_prev_post ) ) {
			$this->_prev_post = $this->app->session->get( $this->get_old_post_session_key(), null );
			if ( empty( $this->_prev_post ) ) {
				$this->_prev_post = [];
			}
			$this->app->session->delete( $this->get_old_post_session_key() );
		}
		if ( $checkbox && ! empty( $this->_prev_post ) ) {
			$default = false;
		}

		return \Technote\Models\Utility::array_get( $this->_prev_post, $name, $default );
	}

	/**
	 * @return string
	 */
	protected function get_old_post_session_key() {
		return '__prev_post';
	}

	/**
	 * @param mixed $data
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function dump( $data, $echo = true ) {
		return $this->get_view( 'include/dump', [ 'data' => $data ], $echo );
	}

	/**
	 * @param string $script
	 * @param int $priority
	 */
	public function add_script( $script, $priority = 10 ) {
		$this->app->minify->register_script( $script, $priority );
	}

	/**
	 * @param string $style
	 * @param int $priority
	 */
	public function add_style( $style, $priority = 10 ) {
		$this->app->minify->register_style( $style, $priority );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param int $priority
	 */
	public function add_script_view( $name, $args = [], $priority = 10 ) {
		$this->add_script( $this->get_view( $name, $args, false, false ), $priority );
	}

	/**
	 * @param string $name
	 * @param array $args
	 * @param int $priority
	 */
	public function add_style_view( $name, $args = [], $priority = 10 ) {
		$this->add_style( $this->get_view( $name, $args, false, false ), $priority );
	}

	/**
	 * @param string $value
	 * @param bool $translate
	 * @param bool $echo
	 * @param bool $escape
	 *
	 * @return string
	 */
	public function h( $value, $translate = false, $echo = true, $escape = true ) {
		if ( $translate ) {
			$value = $this->app->translate( $value );
		}
		if ( $escape ) {
			$value = esc_html( $value );
			$value = nl2br( $value );
		}
		if ( $echo ) {
			echo $value;
		}

		return $value;
	}

	/**
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function id( $echo = true ) {
		return $this->h( $this->app->plugin_name, false, $echo );
	}

	/**
	 * @param array $data
	 * @param bool $echo
	 *
	 * @return int
	 */
	public function n( $data, $echo = true ) {
		$count = count( $data );
		if ( $echo ) {
			echo $count;
		}

		return $count;
	}

	/**
	 * @param string $url
	 * @param string $contents
	 * @param bool $translate
	 * @param bool $new_tab
	 * @param array $args
	 * @param bool $echo
	 * @param bool $escape
	 *
	 * @return string
	 */
	public function url( $url, $contents, $translate = false, $new_tab = false, $args = [], $echo = true, $escape = true ) {
		$overwrite = [
			'href'     => $url,
			'contents' => $this->h( $contents, $translate, false, $escape ),
		];
		if ( $new_tab ) {
			$overwrite['target'] = '_blank';
		}

		return $this->get_view( 'include/url', array_merge( $args, $overwrite ), $echo );
	}

	/**
	 * @param bool $append_version
	 * @param string $q
	 *
	 * @return string
	 */
	private function get_assets_version( $append_version, $q = 'v' ) {
		if ( ! $append_version ) {
			return '';
		}
		$append = $this->apply_filters( 'assets_version' );
		if ( $append !== '' ) {
			if ( $q ) {
				return '?' . $q . '=' . $append;
			}

			return '?' . $append;
		}

		return '';
	}

	/**
	 * @param string $path
	 * @param string $default
	 * @param bool $url
	 * @param bool $append_version
	 *
	 * @return string
	 */
	private function get_assets( $path, $default = '', $url = false, $append_version = true ) {
		if ( empty( $path ) ) {
			return '';
		}

		$path = trim( $path );
		$path = trim( $path, '/' . DS );
		$path = str_replace( '/', DS, $path );

		if ( file_exists( $this->app->define->plugin_assets_dir . DS . $path ) && is_file( $this->app->define->plugin_assets_dir . DS . $path ) ) {
			if ( $url ) {
				return $this->app->define->plugin_assets_url . '/' . str_replace( DS, '/', $path ) . $this->apply_filters( 'get_assets_version', $this->get_assets_version( $append_version ), $append_version );
			}

			return $this->app->define->plugin_assets_dir . DS . $path;
		}
		if ( file_exists( $this->app->define->lib_assets_dir . DS . $path ) && is_file( $this->app->define->lib_assets_dir . DS . $path ) ) {
			if ( $url ) {
				return $this->app->define->lib_assets_url . '/' . str_replace( DS, '/', $path ) . $this->apply_filters( 'get_assets_version', $this->get_assets_version( $append_version ), $append_version );
			}

			return $this->app->define->lib_assets_dir . DS . $path;
		}
		if ( empty( $default ) ) {
			return '';
		}

		return $this->get_assets( $default, '', $url, false );
	}

	/**
	 * @param string $path
	 * @param string $default
	 * @param bool $append_version
	 *
	 * @return string
	 */
	public function get_assets_url( $path, $default = '', $append_version = true ) {
		return $this->get_assets( $path, $default, true, $append_version );
	}

	/**
	 * @param string $path
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_assets_path( $path, $default = '' ) {
		return $this->get_assets( $path, $default );
	}

	/**
	 * @param string $path
	 * @param string $default
	 * @param bool $append_version
	 *
	 * @return string
	 */
	public function get_img_url( $path, $default = 'img/no_img.png', $append_version = true ) {
		return empty( $path ) ? '' : $this->get_assets_url( 'img/' . $path, $default, $append_version );
	}

	/**
	 * @param string $path
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_css_path( $path, $default = '' ) {
		return empty( $path ) ? '' : $this->get_assets_path( 'css/' . $path, $default );
	}

	/**
	 * @param string $path
	 * @param string $default
	 *
	 * @return string
	 */
	protected function get_js_path( $path, $default = '' ) {
		return empty ( $path ) ? '' : $this->get_assets_path( 'js/' . $path, $default );
	}

	/**
	 * @param string $url
	 * @param string $view
	 * @param array $args
	 * @param string $field
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function assets( $url, $view, $args, $field, $echo = true ) {
		return $this->get_view( $view, array_merge( $args, [
			$field => $url,
		] ), $echo );
	}

	/**
	 * @param string $path
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function img( $path, $args = [], $echo = true ) {
		return $this->assets( $this->get_img_url( $path ), 'include/img', $args, 'src', $echo );
	}

	/**
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function loading( $args = [], $echo = true ) {
		return $this->img( 'loading.gif', $args, $echo );
	}

	/**
	 * @param array $args
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function no_img( $args = [], $echo = true ) {
		return $this->img( 'no_img.png', $args, $echo );
	}

	/**
	 * @param string $path
	 * @param int $priority
	 *
	 * @return bool
	 */
	public function css( $path, $priority = 10 ) {
		$css = $this->get_css_path( $path );
		if ( ! empty( $css ) ) {
			$this->app->minify->register_css_file( $css, $priority );

			return true;
		}

		return false;
	}

	/**
	 * @param string $path
	 * @param int $priority
	 *
	 * @return bool
	 */
	public function js( $path, $priority = 10 ) {
		$js = $this->get_js_path( $path );
		if ( ! empty( $js ) ) {
			$this->app->minify->register_js_file( $js, $priority );

			return true;
		}

		return false;
	}

	/**
	 * @param string $handle
	 * @param string $file
	 * @param array $depends
	 * @param string|bool|null $ver
	 * @param string $media
	 */
	public function enqueue_style( $handle, $file, $depends = [], $ver = false, $media = 'all' ) {
		wp_enqueue_style( $handle, $this->app->define->plugin_assets_url . '/css/' . $file, $depends, $ver, $media );
	}

	/**
	 * @param string $handle
	 * @param string $file
	 * @param array $depends
	 * @param string|bool|null $ver
	 * @param bool $in_footer
	 */
	public function enqueue_script( $handle, $file, $depends = [], $ver = false, $in_footer = true ) {
		wp_enqueue_script( $handle, $this->app->define->plugin_assets_url . '/js/' . $file, $depends, $ver, $in_footer );
	}

	/**
	 * setup modal
	 */
	public function setup_modal() {
		$this->add_script_view( 'include/script/modal', [], 1 );
		$this->add_style_view( 'include/style/modal', [], 1 );
	}

	/**
	 * @param bool $echo
	 *
	 * @return string
	 */
	public function modal_class( $echo = true ) {
		return $this->h( $this->get_slug( 'modal_class', '_modal' ), false, $echo );
	}

}
