function init_callback_firebuglite() {
    if (!window.firebug) {

        // from firebug lite bookmarklet
        window.firebug = document.createElement('script');
        firebug.setAttribute('src', 'http://getfirebug.com/releases/lite/1.2/firebug-lite-compressed.js');
        document.body.appendChild(firebug);
        (function() {
            if (window.firebug.version) {
                firebug.init();
            } else {
                setTimeout(arguments.callee);
            }
        })();
        void(firebug);

        if (window.debug && debug.setCallback) {
            (function() {
                if (window.firebug && window.firebug.version) {
                    debug.setCallback(function(level) {
                        var args = Array.prototype.slice.call(arguments, 1);
                        firebug.d.console.cmd[level].apply(window, args);
                    }, true);
                } else {
                    setTimeout(arguments.callee, 100);
                }
            })();
        }
    }
}
init_callback_firebuglite();
