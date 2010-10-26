DOM = {};
    
DOM.xpath = function(query) {
    return document.evaluate(query, document, null,XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
}

DOM.getNode = function(path) {
    var node = DOM.xpath(path);
    if (node.snapshotLength == 1) {
        return node.snapshotItem(0);
    }
    return null;
}

DOM.getNodeValue = function(path, defaultValue, forceToUseTextContent) {
    var node = DOM.getNode(path);
    if (node != null) {
        if (node.value && !forceToUseTextContent) {
            return node.value;
        } else {
            return node.textContent;
        }
    }
    return defaultValue;
}

DOM.getNodes = function(query) {
    return document.evaluate(query, document, null,XPathResult.UNORDERED_NODE_SNAPSHOT_TYPE, null);
};

DOM.getFirstNode = function(path) {
    var value = this.getNodes(path);
    if (value.snapshotLength == 1) {
        return value.snapshotItem(0);
    }
    return null;
};

DOM.getFirstNodeValue = function(path, defaultValue) {
    var value = this.getFirstNode(path);
    if (value != null) {
        return value.value;
    }
    else return defaultValue;
};

DOM.getFirstNodeTextContent = function(path, defaultValue) {
    var value = this.getFirstNode(path);
    if (value != null) {
        return value.textContent;
    }
    else return defaultValue;
};

DOM.createCaller = function(object, methodName, params) {
    var f;
    if (params) {
        f = function() {
            if (!object[methodName]) {
                debugger;
            }
            return object[methodName].apply(object, params);
        }
    } else {
        f = function() {
            if (!object[methodName]) {
                debugger;
            }
            return object[methodName].apply(object, arguments);
        }
    }
    return f;
};

DOM.createElement = function(tagName, attributes, childNodes) {
    var element = document.createElement(tagName);
    
    if (attributes) {
        for (var attribute in attributes) {
            type = typeof attributes[attribute];
            if (type == 'function') {
                if (attribute.substr(0, 2) != 'on') {
                    throw new Error('function attributes must begin with on');
                }
                DOM.addListener(element, attribute.substr(2), attributes[attribute]);
            } else if (type == 'boolean') {
                element[attribute] = attributes[attribute];
            } else if (attribute == 'style' && typeof attributes[attribute] == 'object') {
                styleProperties = attributes[attribute];
                for (item in styleProperties) {
                    if (styleProperties[item] !== null) {
                        element.style[item] = styleProperties[item];
                    }
                }
            } else if (attribute == 'class') {
                element.className = attributes[attribute];
            } else if (attributes[attribute] === null) {
                continue;
            } else if (tagName != 'input' || (attributes[attribute] != 'type' && attributes[attribute] != 'name')) {
                element.setAttribute(attribute, attributes[attribute]);
            }
        }
    }
    
    if (childNodes) {
        for (var i = 0; i < childNodes.length; ++i) {
            if (childNodes[i]) {
                if (typeof childNodes[i] == 'string' || typeof childNodes[i] == 'number') {
                    element.appendChild(document.createTextNode(childNodes[i]));
                } else if (childNodes[i].nodeType == 1) {
                    element.appendChild(childNodes[i]);
                } else if (childNodes[i].widget) {
                    element.appendChild(childNodes[i].widget);
                } else {
                    if (Beastx.debugMode) {
                        Beastx.log('falta implementar otros tipos de datos en core.js createElement');
                    }
                }
            }
        }
    }
    return element;
};

DOM.insertAfter = function(newElement, targetElement) {
    var parent = targetElement.parentNode;
    if (parent.lastchild == targetElement) {
        parent.appendChild(newElement);
    } else {
        parent.insertBefore(newElement, targetElement.nextSibling);
    }
}

DOM.addListener = function(element, eventString, caller) {
    element.addEventListener(eventString, caller, true);
};

DOM.removeListener = function(element, eventString, caller) {
    element.removeEventListener(eventString, caller, true);
};

DOM.isChild = function(containerElement, containedElement) {
    // Element.prototype.DOCUMENT_POSITION_CONTAINS == 8
    return (containedElement.compareDocumentPosition(containerElement) & 8) == 8;
}

DOM.hasClass = function(element, className) {
    return !!element.className && VAR.hasWord(element.className, className)
}

DOM.addClass = function(element, className) {
    element.className = VAR.addWord(element.className, className)
}

DOM.removeClass = function(element, className) {
    element.className = VAR.removeWord(element.className, className)
}

DOM.toggleClass = function(element, className) {
    if (DOM.hasClass(element, className)) {
        DOM.removeClass(element, className);
    } else {
        DOM.addClass(element, className);
    }
}

DOM.setHasClass = function(element, className, addIfTrueRemoveIfFalse) {
    if (addIfTrueRemoveIfFalse) {
        DOM.addClass(element, className);
    } else {
        DOM.removeClass(element, className);
    }
}

DOM.preventDefault = function(event) {
    if (event.preventDefault) {
        event.preventDefault();
    } else {
        event.returnValue = false;
    }
}

DOM.stopPropagation = function(event) {
    if (event.stopPropagation) {
        event.stopPropagation();
    } else {
        event.cancelBubble = true;
    }
}

DOM.cancelEvent = function(event) {
    DOM.preventDefault(event);
    DOM.stopPropagation(event);
}

DOM.appendChildNodes = function(element, childs) {
    for (var i = 0; i < childs.length; ++i) {
        element.appendChild(childs[i]);
    }
}

DOM.removeAllChildNodes = function(element) {
    while (element.childNodes.length > 0) {
        element.removeChild(element.firstChild);
    }
}

DOM.cleanChildNodes = function(element) {
    if (element.nodeType == 1) {
        return element;
    } else {
        return null;
    }
}

DOM.getPosition = function(element, dontWrapBody) {
    var left = 0;
    var top = 0;
    var obj = element;
    while (true) {
        left += obj.offsetLeft;
        top += obj.offsetTop;
        if (!obj.offsetParent) {
            break;
        }
        obj = obj.offsetParent;
    }
    var parentNode = element.parentNode;
    while (parentNode && parentNode != document.body) {
        left -= parentNode.scrollLeft;
        top -= parentNode.scrollTop;
        parentNode = parentNode.parentNode;
    }
    return { x: left, y: top };
}