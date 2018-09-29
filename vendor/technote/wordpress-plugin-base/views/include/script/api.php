<?php
/**
 * Technote Views Include Script Api
 *
 * @version 0.0.0.0.0
 * @author technote-space
 * @since 0.0.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Traits\Presenter $instance */
/** @var string $endpoint */
/** @var string $namespace */
/** @var string $nonce */
/** @var array $functions */
/** @var string $api_class */
/** @var array $scripts */
?>

<script>
    (function () {
        class <?php $instance->h( $api_class );?> {
            constructor() {
                this.endpoint = '<?php $instance->h( $endpoint . $namespace );?>/';
                this.functions = <?php echo json_encode( $functions );?>;
                this.xhr = {};
            }

            ajax(func, args) {
                if (args === undefined) args = {};
                if (this.functions[func]) {
                    const setting = this.functions[func];
                    let url = this.endpoint + setting.endpoint;
                    const method = setting.method.toUpperCase();
                    const config = {
                        method: method,
                    };
                    switch (method) {
                        case 'POST':
                        case 'PUSH':
                            config.data = args;
                            break;
                        default:
                            const query = [];
                            args._ = (new Date()).getTime();
                            for (const prop in args) {
                                if (args.hasOwnProperty(prop)) {
                                    query.push(prop + '=' + encodeURIComponent(args[prop]));
                                }
                            }
                            if (url.indexOf('?') !== -1) {
                                url += '&' + query.join('&');
                            } else {
                                url += '?' + query.join('&');
                            }
                            break;
                    }
                    config.url = url;
                    this.abort(func);
                    return this._ajax(config, func);
                } else {
                    return new Promise((resolve, reject) => {
                        setTimeout(function () {
                            reject(-1, null, null);
                        }, 1);
                    });
                }
            }


            _param(a) {
                const s = [];
                const add = function (key, value) {
                    s[s.length] = encodeURIComponent(key) + "=" + encodeURIComponent(value == null ? "" : value);
                };

                if (Array.isArray(a)) {
                    this._each(a, function () {
                        add(this.name, this.value);
                    });
                } else {
                    for (const prefix in a) {
                        if (a.hasOwnProperty(prefix)) {
                            this._buildParams(prefix, a[prefix], add);
                        }
                    }
                }
                return s.join('&');
            }

            _buildParams(prefix, obj, add) {
                const self = this;
                if (Array.isArray(obj)) {
                    this._each(obj, function (i, v) {
                        self._buildParams(prefix + "[" + (typeof v === "object" && v != null ? i : "") + "]", v, add);
                    });
                } else if ("object" === typeof obj) {
                    for (const name in obj) {
                        self._buildParams(prefix + "[" + name + "]", obj[name], add);
                    }
                } else {
                    add(prefix, obj);
                }
            }

            _each(obj, fn) {
                if (obj.length === undefined) {
                    for (const i in obj) {
                        if (obj.hasOwnProperty(i)) {
                            fn.call(obj[i], i, obj[i]);
                        }
                    }
                }
                else {
                    for (let i = 0, ol = obj.length, val = obj[0];
                         i < ol && fn.call(val, i, val) !== false; val = obj[++i]) {
                    }
                }
                return obj;
            }

            _ajax(config, func) {
                const $this = this;
                return new Promise((resolve, reject) => {
                    const xhr = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();

                    xhr.open(config.method, config.url, true);
                    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                    xhr.setRequestHeader('X-WP-Nonce', '<?php $instance->h( $nonce );?>');
                    xhr.onreadystatechange = function () {
                        if (4 === xhr.readyState) {
                            if (200 === xhr.status) {
                                try {
                                    const json = JSON.parse(xhr.responseText);
                                    resolve(json);
                                } catch (e) {
                                    reject([xhr.status, e, xhr]);
                                }
                            } else {
                                reject([xhr.status, null, xhr]);
                            }
                            $this.xhr[func] = null;
                        }
                    };
                    if (config.data) {
                        xhr.send(this._param(config.data));
                    } else {
                        xhr.send();
                    }
                    this.xhr[func] = xhr;
                });
            }

            abort(func) {
                if (this.xhr[func] !== undefined && this.xhr[func] !== null) {
                    this.xhr[func].abort();
                    this.xhr[func] = null;
                }
            }
        }

        window.<?php $instance->h( $api_class );?> = new <?php $instance->h( $api_class );?> ();
    })();
</script>
