$ = function(selector) {
    return jQuery('#' + selector)[0];
}

jQuery.fn.center = function () {
    this.css("position","absolute");
    this.css("top", ( jQuery(window).height() - this.height() ) / 2+jQuery(window).scrollTop() + "px");
    this.css("left", ( jQuery(window).width() - this.width() ) / 2+jQuery(window).scrollLeft() + "px");
    return this;
}

jQuery(document).ready(function() {
    tinyMCEPreInit.mceInit.height = '200';
    tinyMCEPreInit.mceInit.theme_advanced_resizing = false;
    tinyMCEPreInit.mceInit.editor_selector = "BeastxPluginEditor";
    tinyMCE.init(tinyMCEPreInit.mceInit);
    
    tinyMCEPreInit.mceInit.theme_advanced_buttons1 = 'bold, italic, underline, bullist, numlist, link, unlink';
    tinyMCEPreInit.mceInit.editor_selector = "BeastxPluginEditorSimple";
    tinyMCE.init(tinyMCEPreInit.mceInit);
});



Beastx = {};
    
Beastx.log = function(msg, label) {
    if (console && console.log) {
        console.log(msg, label);
    } else {
        alert((label ? label + ': ' : '') + msg);
    }
}

function New(classRef, constructorArgs, events) {
    var obj = new classRef;
    classRef.prototype.element = function(tagname, attributes, childs) {
        return DOM.createElement(tagname, attributes, childs);
    }
    classRef.prototype.replaceContent = function(parent, newContent) {
        while (parent.firstChild) {
            parent.removeChild(parent.firstChild);
        }
        parent.appendChild(newContent.widget ? newContent.widget : newContent);
    }
    classRef.prototype.removeChild = function(container, element) {
        if (element.nodeType == 1) {
            container.removeChild(element);
        } else if (element.widget) {
            container.removeChild(element.widget);
        }
    }
    classRef.prototype.appendChild = function(container, element) {
        if (typeof element == 'string' || typeof element == 'number') {
            container.appendChild(document.createTextNode(element));
        } else if (element.nodeType == 1) {
            container.appendChild(element);
        } else if (element.widget) {
            container.appendChild(element.widget);
        }
    }
    classRef.prototype.caller = function(callback, params) {
        return DOM.createCaller(this, callback, params);
    }
    obj.listeners = [];
    classRef.prototype.addListener = function(eventName, callback) {
        return this.listeners.push({eventName: eventName.substring(2), callback: callback});
    }
    classRef.prototype.dispatchEvent = function(eventName, params) {
        for (var i = 0; i < this.listeners.length; ++i) {
            if (this.listeners[i].eventName == eventName) {
                this.listeners[i].callback(params);
            }
        }
    }
    if (classRef.prototype.toString.call(obj) == '[object Object]') {
        classRef.prototype.toString = function() {
            return this.scriptName ? this.scriptName : '[object Object]';
        }
    }
    obj.classRef = classRef;
    if (constructorArgs) {
        obj.init.apply(obj, constructorArgs);
    } else if (obj.init) {
        obj.init();
    }
    if (events) {
        for (var event in events) {
            obj.addListener(event, events[event]);
        }
    }
    return obj;
}

function getQueryString(ji, fromString) {
    hu = fromString ? fromString : window.location.search.substring(1);
    gy = hu.split("&");
    for (i=0;i<gy.length;i++) {
        ft = gy[i].split("=");
        if (ft[0] == ji) {
            return ft[1];
        }
    }
    return null;
}
