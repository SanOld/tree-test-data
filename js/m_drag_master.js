(function( $ ) {

    var defaults = {
        'dragElement' : '.drag-element',

        'cloneElement' : false,

        'onDrop' : function(dragObject){},

        'onDragStart' : function() {},

        'onDragMove' : function() {},

        'onDragSuccess' : function() {},

        'onDragFail' : function() {}

    };

    var methods = {
        init : function( params ) {
            var options = $.extend({}, defaults, params);
            var elemId = 'm-drag-master-drop-zone';

            $(options.dragElement).each(function(){
                new DragObject(this, options);
            });

            if ( $(this).prop("id") ){
                elemId = $(this).prop("id");
            } else{
                $(this).prop('id', elemId);
            }

            new DropTarget(document.getElementById( elemId ), options.onDrop);
        }
    };

    $.fn.mDropMaster = function( method ) {

        // логика вызова метода
        if ( methods[method] ) {
            return methods[ method ].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Метод с именем ' +  method + ' не существует' );
        }
    };

    function DragObject(element, options) {
        element.dragObject = this;

        var rememberPosition;
        var mouseOffset;
        var originalElement = element;

        dragMaster.makeDraggable(element);

        this.onDragStart = function(offset) {

            if(options.cloneElement){
                removeElementsByClass("drag-clone");
                element = originalElement.cloneNode(true);
                element.classList.add("drag-clone");
                insertAfter(element, originalElement.firstChild);
            }

            var s = element.style;

            rememberPosition = {top: s.top, left: s.left, position: s.position};
            s.position = 'absolute';

            mouseOffset = offset;

            options.onDragStart.call(this);
        };

        this.hide = function() {
            element.style.display = 'none';
        };

        this.show = function() {
            element.style.display = '';
        };

        this.onDragMove = function(x, y) {

            element.style.top =  y - mouseOffset.y +'px';
            element.style.left = x - mouseOffset.x +'px';

            options.onDragMove.call(this);
        };

        this.onDragSuccess = function(dropTarget) { options.onDragSuccess.call(this); };

        this.onDragFail = function() {
            var s = element.style;
            s.top = rememberPosition.top;
            s.left = rememberPosition.left;
            s.position = rememberPosition.position;

            if(options.cloneElement){
                removeElementsByClass("drag-clone");
            }

            options.onDragFail.call(this);
        };

        this.toString = function() {
            return element.id;
        }
    }

    function DropTarget(element, dropCallback) {

        if( element === null ){
            throw new Error("The drop element is not founded!");
        }

        element.dropTarget = this;

        this.canAccept = function(dragObject) {
            return true;
        };

        this.accept = dropCallback;

        this.onLeave = function() {
            element.classList.remove("uponMe");
        };

        this.onEnter = function() {
            element.classList.add("uponMe");
        };

        this.toString = function() {
            return element.id;
        }
    }

    var dragMaster = (function() {

        var dragObject;
        var mouseDownAt;

        var currentDropTarget;


        function mouseDown(e) {
            e = fixEvent(e);
            if (e.which!=1) return;

            mouseDownAt = { x: e.pageX, y: e.pageY, element: this };

            addDocumentEventHandlers();

            return false
        }


        function mouseMove(e){
            e = fixEvent(e);

            // (1)
            if (mouseDownAt) {
                if (Math.abs(mouseDownAt.x-e.pageX)<5 && Math.abs(mouseDownAt.y-e.pageY)<5) {
                    return false;
                }
                // Начать перенос
                var elem  = mouseDownAt.element;

                // текущий объект для переноса
                dragObject = elem.dragObject;

                dragObject.elem = elem;

                // запомнить, с каких относительных координат начался перенос
                var mouseOffset = getMouseOffset(elem, mouseDownAt.x, mouseDownAt.y);
                mouseDownAt = null; // запомненное значение больше не нужно, сдвиг уже вычислен

                dragObject.onDragStart(mouseOffset); // начали

            }

            // (2)
            dragObject.onDragMove(e.pageX, e.pageY);

            // (3)
            var newTarget = getCurrentTarget(e);

            // (4)
            if (currentDropTarget != newTarget) {
                if (currentDropTarget) {
                    currentDropTarget.onLeave();
                }
                if (newTarget) {
                    newTarget.onEnter();
                }
                currentDropTarget = newTarget;

            }

            // (5)
            return false;
        }


        function mouseUp(){
            if (!dragObject) { // (1)
                mouseDownAt = null;
            } else {
                // (2)
                if (currentDropTarget) {
                    currentDropTarget.accept(dragObject);
                    dragObject.onDragSuccess(currentDropTarget);
                } else {
                    dragObject.onDragFail();
                }

                dragObject = null;
            }

            // (3)
            removeDocumentEventHandlers();
        }


        function getMouseOffset(target, x, y) {

            if(document.getElementsByClassName('drag-clone')[0]){
                target = document.getElementsByClassName('drag-clone')[0];
            }

            var docPos	= getOffset(target);
            return {x:x - docPos.left, y:y - docPos.top}
        }


        function getCurrentTarget(e) {
            // спрятать объект, получить элемент под ним - и тут же показать опять

            if (navigator.userAgent.match('MSIE') || navigator.userAgent.match('Gecko')) {
                var x = e.clientX, y = e.clientY;
            } else {
                var x = e.pageX, y = e.pageY;
            }
            // чтобы не было заметно мигание - максимально снизим время от hide до show
            dragObject.hide();
            var elem = document.elementFromPoint(x,y);
            dragObject.show();

            // найти самую вложенную dropTarget
            while (elem) {
                // которая может принять dragObject
                if (elem.dropTarget && elem.dropTarget.canAccept(dragObject)) {
                    return elem.dropTarget;
                }
                elem = elem.parentNode;
            }

            // dropTarget не нашли
            return null;
        }


        function addDocumentEventHandlers() {
            document.onmousemove = mouseMove;
            document.onmouseup = mouseUp;
            document.ondragstart = document.body.onselectstart = function() {return false}
        }
        function removeDocumentEventHandlers() {
            document.onmousemove = document.onmouseup = document.ondragstart = document.body.onselectstart = null;
        }


        return {

            makeDraggable: function(element){
                element.onmousedown = mouseDown;
            }
        }
    }());

    // helpers

    function removeElementsByClass(className){
        var elements = document.getElementsByClassName(className);
        while(elements.length > 0){
            elements[0].parentNode.removeChild(elements[0]);
        }
    }

    function insertAfter(elem, refElem) {
        return refElem.parentNode.insertBefore(elem, refElem.nextSibling);
    }

    function fixEvent(e) {
        // получить объект событие для IE
        e = e || window.event;

        // добавить pageX/pageY для IE
        if ( e.pageX == null && e.clientX != null ) {
            var html = document.documentElement;
            var body = document.body;
            e.pageX = e.clientX + (html && html.scrollLeft || body && body.scrollLeft || 0) - (html.clientLeft || 0);
            e.pageY = e.clientY + (html && html.scrollTop || body && body.scrollTop || 0) - (html.clientTop || 0);
        }

        // добавить which для IE
        if (!e.which && e.button) {
            e.which = e.button & 1 ? 1 : ( e.button & 2 ? 3 : ( e.button & 4 ? 2 : 0 ) );
        }

        return e
    }

    function getOffset(elem) {
        if (elem.getBoundingClientRect) {
            return getOffsetRect(elem);
        } else {
            return getOffsetSum(elem);
        }
    }

    function getOffsetRect(elem) {
        var box = elem.getBoundingClientRect();

        var body = document.body;
        var docElem = document.documentElement;

        var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop;
        var scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
        var clientTop = docElem.clientTop || body.clientTop || 0;
        var clientLeft = docElem.clientLeft || body.clientLeft || 0;
        var top  = box.top +  scrollTop - clientTop;
        var left = box.left + scrollLeft - clientLeft;

        return { top: Math.round(top), left: Math.round(left) }
    }

    function getOffsetSum(elem) {
        var top=0, left=0;
        while(elem) {
            top = top + parseInt(elem.offsetTop);
            left = left + parseInt(elem.offsetLeft);
            elem = elem.offsetParent;
        }

        return {top: top, left: left}
    }

})(jQuery);
