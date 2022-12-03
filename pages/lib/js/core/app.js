

/*************  The FrigateApp  *************/
;(function($, root, document, undefined) {

    'use strict';

    /*** Register plugin in window object */
	
	root.FrigateApp = function()
	{
		let defaults = {
        };
		
		this.bsModals = {};
		this.settings = (arguments[0] && typeof arguments[0] === 'object') ? extendDefaults(defaults,arguments[0]) : defaults;
		
		this.init();
	}
	
	/*** Public Methods */
	
	FrigateApp.prototype.init = function()
	{
		console.log('Init plugin.');
		
		build.call(this);

        $.fn.modal.Constructor.prototype.enforceFocus = function() {};
	}
	
	FrigateApp.prototype.onActions = function(actions = "click", data = "data-action", events) {
        //Register events:
        $(document).on(actions, `[${data}]`, function(ev) {
            let operation = $(this).attr(data);
            let eventtype = ev.type;
            if (events.hasOwnProperty(`${eventtype} ${operation}`)) {
                events[`${eventtype} ${operation}`].call(this, ev);
            }
        });
    }

    FrigateApp.prototype.buildEndpointUrl = function(endpoint) {
        let base = document.querySelector("meta[name='baseurl']").getAttribute("content");
        return [base, endpoint].join('/').replace(/([^:]\/)\/+/g, "$1");
    }

    FrigateApp.prototype.apiRequest = function(type = "POST", path = "", base = "", _data = {}, handlers = {}, formData = false) {
        //Set url:
        base = base ? base : document.querySelector("meta[name='baseurl']").getAttribute("content");
        //Tokenize the request:
        let data = {
            'csrf' : document.querySelector("meta[name='csrf']").getAttribute('content') ?? ""
        };
        //Prep the data - normal object or formData:
        let prepData;
        if (formData) {
            prepData = new FormData();
            prepData.append('csrf_token', data.request_token);
            for (const key in _data) {
                prepData.append(key,  _data[key]);
            }
        } else {
            prepData = $.extend(data, _data);
        }
        //Build request:
        let ajaxSet = {
            type: type,
            dataType: 'json',
            accepts: {
                json: 'application/json'
            },
            data: prepData,
            success: function(data) {},
            error: function(jqXhr, textStatus, errorMessage) {
                console.log("ERROR on AJAX", errorMessage);
                console.log("ERROR on AJAX", jqXhr);
                console.log("ERROR on AJAX", textStatus);
            },
            complete: function(data) {
                //console.log(data);
            },
        };
        //If formdata add some settings:
        if (formData) {
            ajaxSet.contentType = false;
            ajaxSet.enctype     = 'multipart/form-data';
            ajaxSet.processData = false;
        }
        //Extend settings & handlers:
        $.extend(ajaxSet, handlers);
        //join base and path to form url avoid multiple slashes between them
        let url = [base, path].join('/').replace(/([^:]\/)\/+/g, "$1");
        //console.log(url, ajaxSet);
        //Make request:
        return $.ajax(url, ajaxSet);
    }
	
	FrigateApp.helpers = {
        pagination : function(c, m) {
            var current = c,
                last = m,
                delta = 2,
                left = current - delta,
                right = current + delta + 1,
                range = [],
                rangeWithDots = [],
                l;
        
            for (let i = 1; i <= last; i++) {
                if (i == 1 || i == last || i >= left && i < right) {
                    range.push(i);
                }
            }
        
            for (let i of range) {
                if (l) {
                    if (i - l === 2) {
                        rangeWithDots.push(l + 1);
                    } else if (i - l !== 1) {
                        rangeWithDots.push('...');
                    }
                }
                rangeWithDots.push(i);
                l = i;
            }
        
            return rangeWithDots;
        },
        types : {
            isObject : function(obj) {
                return obj === Object(obj);
            },
            isString : function(str) {
                return typeof str === 'string' || str instanceof String;
            },
            isNumber : function(num) {
                return typeof num === 'number' && isFinite(num);
            },
            isNumeric : function(numeric) {
                return !isNaN(parseFloat(numeric)) && isFinite(numeric);
            },
            isBoolean : function(bool) {
                return typeof bool === 'boolean';
            },
            isFunction : function(func) {
                return typeof func === 'function';
            },
            isUndefined : function(undef) {
                return typeof undef === 'undefined';
            },
            isNull : function(nul) {
                return nul === null;
            },
            isDate : function(date) {
                return date instanceof Date;
            },
            isArray : function(arr) {
                return Array.isArray(arr);
            },
            isRegExp : function(reg) {
                return reg instanceof RegExp;
            },
            isSymbol : function(sym) {
                return typeof sym === 'symbol';
            },
            isSet : function(set) {
                return set instanceof Set;
            },
            isMap : function(map) {
                return map instanceof Map;
            },
            isWeakSet : function(wset) {
                return wset instanceof WeakSet;
            },
            isWeakMap : function(wmap) {
                return wmap instanceof WeakMap;
            },
            isPromise : function(prom) {
                return prom instanceof Promise;
            },
            isGenerator : function(gen) {
                return gen instanceof Generator;
            },
            isGeneratorFunction : function(genfunc) {
                return genfunc instanceof GeneratorFunction;
            },
            isAsyncFunction : function(asyncfunc) {
                return asyncfunc instanceof AsyncFunction;
            },
            isTypedArray : function(tarr) {
                return tarr instanceof TypedArray;
            },
            isBlob : function(blob) {
                return blob instanceof Blob;
            },
            isFile : function(file) {
                return file instanceof File;
            },
            isFileList : function(filelist) {
                return filelist instanceof FileList;
            },
            isHTMLDocument : function(html) {
                return html instanceof HTMLDocument;
            },
            isXMLDocument : function(xml) {
                return xml instanceof XMLDocument;
            },
            isHTMLElement : function(html) {
                return html instanceof HTMLElement;
            },
            isSVGElement : function(svg) {
                return svg instanceof SVGElement;
            },
            isJson : function(json) {
                try {
                    JSON.parse(json);
                } catch (e) {
                    return false;
                }
                return true;
            },
            isBase64 : function(base64) {
                try {
                    return btoa(atob(base64)) == base64;
                } catch (e) {
                    return false;
                }
            },
            isMD5 : function(md5) {
                return /^[a-f0-9]{32}$/.test(md5);
            },
            isSHA1 : function(sha1) {
                return /^[a-f0-9]{40}$/.test(sha1);
            },
            isSHA256 : function(sha256) {
                return /^[a-f0-9]{64}$/.test(sha256);
            },
            isSHA512 : function(sha512) {
                return /^[a-f0-9]{128}$/.test(sha512);
            },
            isHex : function(hex) {
                return /^[a-f0-9]+$/.test(hex);
            },
            isBinary : function(bin) {
                return /^[01]+$/.test(bin);
            },
            isOctal : function(oct) {
                return /^[0-7]+$/.test(oct);
            }
        },
        validation : {
            /*
             * validateEmail
             * validate an email address
             * @param email
             * @return boolean
             */
            validateEmail : function(email) {
                return String(email).toLowerCase()
                            .match(/^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/);
            },
            /*
             * validatePassword
             * validate a password
             * @param password
             * @param min
             * @param max
             * @param special
             * @param number
             * @param upper
             * @param lower
             * @return boolean
             */
            validatePassword : function(password, min = 8, max = 32, special = true, number = true, upper = true, lower = true) {
                let regex = new RegExp(`^(?=.*[a-z]${lower ? "" : ""})(?=.*[A-Z]${upper ? "" : ""})(?=.*[0-9]${number ? "" : ""})(?=.*[!@#$%^&*]${special ? "" : ""})[a-zA-Z0-9!@#$%^&*]{${min},${max}}$`);
                return String(password).match(regex);
            },
            /*
             * validatePhone
             * validate a phone number with optional country code
             * @param phone
             * @return boolean
             */
            validatePhone : function(phone) {
                return String(phone).match(/^\+?[0-9]{10,14}$/);
            },
            /*
             * validateName
             * validate a name (first, last, etc)
             * @param name
             * @return boolean
             */
            validateName : function(name) {
                return String(name).match(/^[a-zA-Z]+(([',. -][a-zA-Z ])?[a-zA-Z]*)*$/);
            },
            /*
             * validateDomain
             * validate a domain name e.g. google.com with optional subdomain and without protocol
             * @param domain
             * @return boolean
             */
            validateDomain : function(domain) {
                return String(domain).match(/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/);
            },
            /*
             * validateUrl
             * validate a url with protocol (http or https).
             * @param url
             * @return boolean
             */
            validateUrl : function(string) {
                let url;
                try {
                    url = new URL(string);
                } catch (_) {
                    return false;  
                }
                return url.protocol === "http:" || url.protocol === "https:";
            },
            /*
             * validateIp
             * validate an ip address
             * @param ip
             * @return boolean
             */
            validateIp : function(ip) {
                return String(ip).match(/^([0-9]{1,3}\.){3}[0-9]{1,3}$/);
            },
            /*
             * validateIpRange
             * validate an ip range
             * @param ip
             * @return boolean
             */
            validateIpRange : function(ip) {
                return String(ip).match(/^([0-9]{1,3}\.){3}[0-9]{1,3}\/[0-9]{1,2}$/);
            },
            /*
             * validateDate
             * validate a date in a specific format
             * @param date
             * @param format
             * @return boolean
             */
            validateDate : function(date, format = "YYYY-MM-DD") {
                let regex = new RegExp(`^${format.replace(/Y/g, "[0-9]{4}").replace(/M/g, "[0-9]{2}").replace(/D/g, "[0-9]{2}")}$`);
                return String(date).match(regex);
            }
        },
        objects : {
            /* 
             * length
             * get the length of an object
             * @param object
             * @return int
             */
            length : function(object) {
                return Object.keys(object).length;
            },
            /*
             * isEmpty
             * check if an object is empty
             * @param object
             * @return boolean
             */
            isEmpty : function(object) {
                return Object.keys(object).length === 0;
            },
            /*
             * keys
             * get the keys of an object
             * @param object
             * @return array
             */
            keys : function(object) {
                return Object.keys(object);
            },
            /*
             * values
             * get the values of an object
             * @param object
             * @return array
             */
            values : function(object) {
                return Object.values(object);
            },
            /*
             * hasKey
             * check if an object has a key
             * @param object
             * @param key
             * @return boolean
             */
            hasKey : function(object, key) {
                return object.hasOwnProperty(key);
            },
            /*
             * hasValue
             * check if an object has a value
             * @param object
             * @param value
             * @return boolean
             */
            hasValue : function(object, value) {
                return Object.values(object).includes(value);
            },
            /* clone
             * clone an object
             * @param object
             * @return object
             */
            clone : function(object) {
                return JSON.parse(JSON.stringify(object));
            },
            /*
             * extend
             * extend an object with other objects
             * @param object
             * @param objects
             * @return object
             */
            extend : function(object, ...objects) {
                return Object.assign(object, ...objects);
            }
        },
        arrays : {
            /*
             * unique
             * remove duplicate values from an array
             * @param array
             * @return array
             */
            unique : function(array) {
                return array.filter(function(el, index, arr) {
                    return index == arr.indexOf(el);
                });
            },
            /*
             * removeValue
             * remove a value from an array
             * @param array
             * @param value
             * @return array
             */
            removeValue : function(array, value) {
                return array.filter(function(el) {
                    return el != value;
                });
            },
            /*
             * removeIndex
             * remove a value from an array by index position
             * @param array
             * @param index
             * @return array
             * @return array
             */
            removeIndex : function(array, index) {
                return array.filter(function(el, i) {
                    return i != index;
                });
            },
            /*
             * clean
             * array clean - remove null, undefined and empty values from an array
             * @param array
             * @return array
             * @return array
             */
            clean : function(array) {
                return array.filter(function(el) {
                    return el != null || el != undefined || el != "" || el != [] || el != {};
                });
            },
            /*
             * keySort
             * sort an array by key
             * @param array
             * @param key
             * @return array
             */
            keySort : function(array, key) {
                return array.sort(function(a, b) {
                    var x = a[key]; var y = b[key];
                    return ((x < y) ? -1 : ((x > y) ? 1 : 0));
                });
            },
            /*
             * valueSort
             * sort an array by value
             * @param array
             * @param direction
             * @return array
             */
            valueSort : function(array, direction = "asc") {
                return array.sort(function(a, b) {
                    if (direction == "asc") {
                        return a - b;
                    } else {
                        return b - a;
                    }
                });
            },
            /*
             * shuffle
             * shuffle an array
             * @param array
             * @return array
             */
            shuffle : function(array) {
                return array.sort(function() { return 0.5 - Math.random() });
            },
            /*
             * chunk
             * chunk an array into smaller arrays - split an array into chunks of a specific size
             * e.g. [1,2,3,4,5,6,7,8,9] -> [[1,2,3],[4,5,6],[7,8,9]]
             * @param array
             * @param size
             * @return array
             */
            chunk : function(array, size) {
                return array.reduce((acc, val, i) => {
                    if (i % size === 0) {
                        acc.push([val]);
                    } else {
                        acc[acc.length - 1].push(val);
                    }
                    return acc;
                }, []);
            },
            /*
             * flatten
             * flatten an array - convert a multidimensional array into a single array
             * e.g. [[1,2],[3,4]] -> [1,2,3,4]
             * @param array
             * @return array
             */
            flatten : function(array) {
                return array.reduce((acc, val) => acc.concat(val), []);
            },
            /*
             * clone
             * clone an array
             * @param array
             * @return array
             */
            clone : function(array) {
                return JSON.parse(JSON.stringify(array));
            }
        },
        strings : {
            /*
             * upperCaseFirst
             * uppercase first letter of a string: "hello world" -> "Hello world"
             * @param string
             * @return string
             */
            upperCaseFirst : function(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            },
            /*
             * upperCaseWords
             * uppercase first letter of each word in a string: "hello world" -> "Hello World"
             * @param string
             * @return string
             */
            upperCaseWords : function(string) {
                return string.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
            },
            /*
             * escapeHtml
             * escape html characters in a string
             * @param string
             * @return string
             */
            escapeHtml : function(string) {
                var entityMap = {
                    "&": "&amp;",
                    "<": "&lt;",
                    ">": "&gt;",
                    '"': '&quot;',
                    "'": '&#39;',
                    "/": '&#x2F;'
                };
                return String(string).replace(/[&<>"'\/]/g, function (s) {
                    return entityMap[s];
                });
            },
            /*
             * urlify
             * urlify a string - example: "https://www.google.com" will be converted to <a href="https://www.google.com">https://www.google.com</a>
             * @param string
             * @param text - optional - if not provided the url will be used as text
             * @return string
            */
            urlify : function(string, text = null) {
                var urlRegex = /(https?:\/\/[^\s]+)/g;
                return string.replace(urlRegex, function(url) {
                    return '<a href="' + url + '">' + ( text ?? url ) + '</a>';
                })
            },
            /*
             * urlEncode
             * convert string to url encoded - example: "https://www.google.com" will be converted to "https%3A%2F%2Fwww.google.com"
             * @param string
             * @return string
             */
            urlEncode : function(string) {
                return encodeURIComponent(string);
            },
            /*
             * urlDecode
             * convert string to url decoded - example: "https%3A%2F%2Fwww.google.com" will be converted to "https://www.google.com"
             * @param string
             * @return string
             */
            urlDecode : function(string) {
                return decodeURIComponent(string);
            },
            /* 
             * xss
             * convert string to xss safe string - example: "<script>alert('xss');</script>" will be converted to "&lt;script&gt;alert('xss');&lt;/script&gt;"
             * @param string
             * @return string
             */
            xss : function(string) {
                return string.replace(/&/g, '&amp;')
                             .replace(/</g, '&lt;')
                             .replace(/>/g, '&gt;')
                             .replace(/"/g, '&quot;')
                             .replace(/'/g, '&#x27;')
                             .replace(/\//g, '&#x2F;');
            },
            /* 
             * slugify
             * convert string to slug - example: "Hello World" will be converted to "hello-world"
             * @param string
             * @return string
             */
            slugify : function(string) {
                return string
                    .toString()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '')
                    .toLowerCase()
                    .trim()
                    .replace(/[\s\W-]+/g, '-');
            },
            /*
             * camelCase
             * convert string to camelCase - example: "hello world" will be converted to "helloWorld"
             * @param string
             * @return string
             */
            camelCase : function(string) {
                return string.replace(/(?:^\w|[A-Z]|\b\w)/g, function(word, index) {
                    return index == 0 ? word.toLowerCase() : word.toUpperCase();
                }).replace(/\s+/g, '');
            },
            /*
             * snakeCase
             * convert string to snake_case - example: "hello world" will be converted to "hello_world"
             * @param string
             * @return string
             */
            snakeCase : function(string) {
                return string.replace(/(?:^\w|[A-Z]|\b\w)/g, function(word, index) {
                    return index == 0 ? word.toLowerCase() : "_" + word.toLowerCase();
                }).replace(/\s+/g, '');
            },
            /*
             * kebabCase
             * convert string to kebab-case - example: "hello world" will be converted to "hello-world" 
             * @param string
             * @return string
             */
            kebabCase : function(string) {
                return string.replace(/(?:^\w|[A-Z]|\b\w)/g, function(word, index) {
                    return index == 0 ? word.toLowerCase() : "-" + word.toLowerCase();
                }).replace(/\s+/g, '');
            },
            /*
             * pascalCase
             * convert string to PascalCase - example: "hello world" will be converted to "HelloWorld"
             * @param string
             * @return string
             */
            pascalCase : function(string) {
                return string.replace(/(?:^\w|[A-Z]|\b\w)/g, function(word, index) {
                    return word.toUpperCase();
                }).replace(/\s+/g, '');
            },
            /*
             * titleCase
             * convert string to Title Case:
             * "this is a title" -> "This Is a Title"
            */
            titleCase : function(string) {
                return string.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
            },
        },
        encrypt : {
        }
    };

	/*** Private Methods */
	
	function build()
	{
		let modals = document.querySelectorAll('[data-modal]');
        
        //Iterate over modals:
        for (let i = 0; i < modals.length; i++) {
            //Get modal:
            let modal = modals[i];
            //Get modal options:
            let modalOptions = modal.getAttribute('data-modal-options');
            //Parse options:
            modalOptions = modalOptions ? JSON.parse(modalOptions) : {};
            //Create modal instance:
            let modalInstance = new bootstrap.Modal(modal, modalOptions);
            //modal name:
            let modalName = modal.getAttribute('data-modal');

            //Add modal to modals array:
            this.bsModals[modalName] = modalInstance;
        }
	}
	
	
	function extendDefaults(defaults,properties)
	{
		Object.keys(properties).forEach(property => {
			if(properties.hasOwnProperty(property))
			{
				defaults[property] = properties[property];
			}
		});
		return defaults;
	}

    // Alising:
    FrigateApp._ = FrigateApp.helpers;
    root.Frigate = root.FrigateApp;
    root._h = Frigate.helpers;

})(jQuery, this, document);



/************* Set user actions **********/
;(function($, root, document, undefined) {
    'use strict';
    
    root.globalActions = {

        'click logout': function(ev) {
            //console.log('Action logout', ev);
            $('form#form-logout').submit();
        }
    
    };

})(jQuery, this, document);