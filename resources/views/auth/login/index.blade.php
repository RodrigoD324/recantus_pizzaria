<x-layout title="Acessar">
    <div class="row h-100" style="--bs-gutter-x: 0;">
        <div class="col-8 d-none d-md-flex align-items-center justify-content-center"
            style="background-color: #F3F4F4;">
            <img src="{{ asset('assets/imgs/pizza-maker.png') }}" style="width: 70%;" class="img-fluid"
                alt="people-talking-with-their-phones">
        </div>
        <div class="col-12 col-md-4 d-flex align-items-center">
            <div class="container w-75">
                <!-- <img src="{{ asset('assets/imgs/chat.png') }}" class="img-fluid" alt="ballon-chat"> -->
                <h1 class="display-5 text-center mb-2 ">Pizzaria Recantu's</h1>
                <p class="text-center mb-5">Monitore e gerencie sua pizzaria de forma eficiente.</p>
                <!-- <div class="card">
                    <div class="card-body"> -->
                <form>
                    @csrf
                    <div class="mb-3">
                        <label for="cpf" class="form-label">CPF:</label>
                        <input type="cpf" class="form-control" id="cpf" placeholder="000.000.000-00" autofocus>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Senha:</label>
                        <input type="password" class="form-control" id="password" placeholder="***********">
                    </div>
                    <button type="button"
                        class="btn btn-primary btn-lg w-100 d-flex align-items-center justify-content-center"
                        id="btn_login">
                        <span class="material-symbols-outlined me-1">login</span>
                        Acessar
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layout>

<script src="https://accounts.google.com/gsi/client" async defer></script>
<script src="{{ asset('js/app/auth/login/login.js') }}"></script>