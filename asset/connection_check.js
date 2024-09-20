$(function () {
    let connectionCheck = {
        buttonClass: 'qrmq_check-connection',

        init() {
            $(document)
                .on('click', '.' + this.buttonClass, e => this.check(e));
        },

        check(e) {
            e.preventDefault();
            this.ajax('checkConnection')
                .then(response => {
                    if (response.data.success) {
                        $('.qrmq_connection-check-success').css("display", "inline");
                    } else {
                        $('.qrmq_connection-check-failure').css("display", "inline");
                        $('.qrmq_connection-check-message').text(response.data.message)
                    }
                })
        },

        ajax(action) {
            return BX.ajax.runAction(`qsoft:rabbitmq.api.Controller.${action}`);
        },
    };
    connectionCheck.init();
});
