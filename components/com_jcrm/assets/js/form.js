function getScript(url, success) {
    let script = document.createElement('script');
    script.src = url;
    let head = document.getElementsByTagName('head')[0], done = false;
    // Attach handlers for all browsers
    script.onload = script.onreadystatechange = function() {
        if (!done && (!this.readyState || this.readyState === 'loaded' || this.readyState === 'complete')) {
            done = true;
            success();
            script.onload = script.onreadystatechange = null;
            head.removeChild(script);
        }
    };
    head.appendChild(script);
}

