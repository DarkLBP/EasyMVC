class EasyMVC {
    constructor() {
        let path = window.location.pathname;
        if (path.startsWith('/')) {
            path = path.substring(1);
        }
        if (path.endsWith('/')) {
            path = path.substring(0, path.length - 1);
        }
        const chunks = path.split('/');

        this.controller = "main";
        this.action = "index";
        this.parameters = [];

        if (chunks.length === 1) {
            this.controller = chunks[0];
        } else if (chunks.length > 1) {
            this.controller = chunks[0];
            this.action = chunks[1];
        }
        for (let i = 2; i < chunks.length; i++) {
            this.parameters.push(chunks[i]);
        }
    }

    getAction() {
        return this.action;
    }

    getController() {
        return this.controller;
    }

    getParameters() {
        return this.parameters;
    }

    static getURL(controller = '', action = '', parameters = []) {
        let url = window.location.protocol + '//' + window.location.host + '/';
        if (controller.length > 0) {
            url += controller;
        }
        if (action.length > 0) {
            url += '/' + action;
        }
        if (parameters.length > 0) {
            if (action.length === 0) {
                url += '/';
            }
            for (let i in parameters) {
                url += '/' + parameters[i];
            }
        }
        return url;
    }

    static redirect(url) {
        window.location = url;
    }
}
