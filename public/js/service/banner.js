(function (window) {
    /**
     * Get XML HTTP request object
     */
    function getXmlHttp() {
        var xmlhttp;

        if ("XDomainRequest" in window && window.XDomainRequest != null) {
            xmlhttp = new XDomainRequest();
        } else if (typeof XMLHttpRequest != 'undefined') {
            xmlhttp = new XMLHttpRequest();
        } else {
            try {
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                try {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                } catch (e) {
                    xmlhttp = false;
                }
            }
        }

        return xmlhttp;
    }

    /**
     * Execute body scripts
     * @param bodyElement
     */
    function execBodyScripts(bodyElement) {
        function nodeName(elem, name) {
            return elem.nodeName && elem.nodeName.toUpperCase() === name.toUpperCase();
        }

        function evalScript(elem) {
            var data = (elem.text || elem.textContent || elem.innerHTML || "" ),
                head = document.getElementsByTagName("head")[0] || document.documentElement,
                script = document.createElement("script");

            script.type = "text/javascript";

            try {
                // doesn't work on ie...
                script.appendChild(document.createTextNode(data));
            } catch(e) {
                // IE has funky script nodes
                script.text = data;
            }

            head.insertBefore(script, head.firstChild);
            head.removeChild(script);
        }

        // main section of function
        var scripts = [],
            script,
            children_nodes = bodyElement.getElementsByTagName("script"),
            child,
            i;

        for (i = 0; children_nodes[i]; i++) {
            child = children_nodes[i];

            if (nodeName(child, "script") && (!child.type || child.type.toLowerCase() === "text/javascript")) {
                scripts.push(child);
            }
        }

        for (i = 0; scripts[i]; i++) {
            script = scripts[i];

            if (script.parentNode) {
                script.parentNode.removeChild(script);
            }

            evalScript(scripts[i]);
        }
    }

    /**
     * Insert new element after the specified element
     * @param element
     * @param newElement
     */
    function insertAfter(element, newElement) {
        if (element.nextSibling) {
            element.parentNode.insertBefore(newElement, element.nextSibling);
        } else {
            element.parentNode.appendChild(newElement);
        }
    }

    /**
     * Create banner
     * @param answer
     * @param companyId
     * @param style
     */
    function createBanner(answer, companyId, style)
    {
        var links = document.getElementsByTagName("a");

        for (var i = 0; i < links.length; i++) {
            if (links[i].id == "revudio-company-" + companyId + '-' + style ||
                links[i].id == "revudio-company-" + companyId) {
                var div = document.createElement("div");
                div.innerHTML = answer.message;

                var companyLink = links[i].href;

                div.onclick = function () {
                    window.open(companyLink);
                };

                insertAfter(links[i], div);
                links[i].parentNode.removeChild(links[i]);

                execBodyScripts(div);

                break;
            }
        }

        var el = document.getElementById("revudio-company-" + companyId + "-" + style);

        if (el) {
            el = el.getElementsByTagName("br");

            if (el && el[0]) {
                el[0].style.display = "none";
            }
        }

        el = document.getElementById("revudio-company-" + companyId);

        if (el) {
            el = el.getElementsByTagName("br");

            if (el && el[0]) {
                el[0].style.display = "none";
            }
        }
    }

    /**
     * Process response
     * @param response
     * @param companyId
     * @param style
     */
    function processResponse(response, companyId, style) {
        var jsonAnswer = eval("(" + response + ")");

        if (jsonAnswer.result) {
            createBanner(jsonAnswer, companyId, style);
        }

        if (_revudio.length > 0) {
            loadBanner(_revudio.pop());
        }
    }

    /**
     * Load banner
     * @param config
     */
    function loadBanner(config) {
        var companyId, url, ajax, style;

        if (!('company' in config) || !('domain' in config) || !('style' in config)) {
            return;
        }

        companyId = config.company;
        style = config.style;

        if (isNaN(companyId) || (style != 'small' && style != 'vertical' && style != 'square')) {
            return;
        }

        url = config.domain + '/service/banner?format=json';
        ajax = getXmlHttp();

        if (!ajax) {
            return;
        }

        if ("XDomainRequest" in window && ajax instanceof XDomainRequest) {
            ajax.open('POST', url);
            ajax.onload = function () {
                processResponse(ajax.responseText, companyId, style);
            };
        } else {
            ajax.open('POST', url, true);
            ajax.setRequestHeader('X-PINGARUNER', 'pingpong');
            ajax.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            ajax.onreadystatechange = function() {
                if (ajax.readyState != 4) {
                    return;
                }

                if (ajax.status == 200) {
                    processResponse(ajax.responseText, companyId, style);
                }
            };
        }

        ajax.send('company_id=' + companyId + '&style=' + style);
    }

    if (typeof _revudio != 'undefined' && _revudio.length > 0) {
        loadBanner(_revudio.pop());
    }
})(window);