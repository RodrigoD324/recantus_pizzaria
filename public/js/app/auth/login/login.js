$(function () {
    const validateInputs = function () {
        let cpf = $("#cpf").val();
        let password = $("#password").val();
        if (cpf == "") {
            $("#cpf").focus();
            showToast("info", "Preencher campo de CPF.");
            return false;
        };
        if (password == "") {
            $("#password").focus();
            showToast("info", "Preencher campo de senha.");
            return false;
        };
        return true;
    };

    const requestLogin = function () {
        spinner.show();
        let cpf = $("#cpf").val();
        let password = $("#password").val();
        request.ajax(
            "/auth/login",
            { cpf: cpf, password: password },
            function (response) {
                if (response.message == "UserNotExist" || response.message == "PasswordNotMatch") {
                    $("#cpf").val("");
                    $("#password").val("");
                    $("#cpf").focus();
                    spinner.hide();
                    return showToast("error", "CPF ou senha inválido.");
                };
                if (!response.success) {
                    spinner.hide();
                    return showToast("error", "Erro interno, contate o suporte.");
                };
                if (response.message == "UserAuthenticated") {
                    spinner.hide();
                    return window.location.href = "/chat";
                };
            },
            function (error) {
                spinner.hide();
                console.error(error);
                return showToast("error", "Erro interno, contate o suporte.");
            }
        );
    };

    const actionPressLogin = function () {
        $("#btn_login").on("click", function () {
            let filledInputs = validateInputs();
            if (filledInputs) requestLogin();
        });
    };

    const actionEnterCpfPassword = function () {
        $("#cpf, #password").on("keyup", function (e) {
            if (e.key == "Enter") $("#btn_login").click();
        });
    };

    actionPressLogin();
    actionEnterCpfPassword();
});