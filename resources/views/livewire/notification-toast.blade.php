<div>
    <div class="toast-container">
        <div x-data="{ 
                show: @entangle('show'),
                timer: null,
                startTimer() {
                    if (this.timer) clearTimeout(this.timer);
                    this.timer = setTimeout(() => { this.show = false }, 5000);
                }
             }" x-init="$watch('show', value => { if (value) startTimer() })" x-show="show" x-cloak
            x-transition:enter="toast-enter" x-transition:enter-start="toast-enter-start"
            x-transition:enter-end="toast-enter-end" x-transition:leave="toast-leave" class="custom-toast {{ $type }}">

            <div class="toast-content">
                <div class="toast-icon-wrapper">
                    @if($type === 'success')
                        <svg viewBox="0 0 20 20" fill="#10b981">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @elseif($type === 'error')
                        <svg viewBox="0 0 20 20" fill="#ef4444">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @elseif($type === 'warning')
                        <svg viewBox="0 0 20 20" fill="#f59e0b">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @else
                        <svg viewBox="0 0 20 20" fill="#3b82f6">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd"></path>
                        </svg>
                    @endif
                </div>

                <div class="toast-text">
                    <div class="toast-title">{{ $title }}</div>
                    <div class="toast-message">{{ $message }}</div>
                </div>

                <button @click="show = false" class="toast-close">✕</button>
            </div>

            <div class="toast-progress-container">
                <div class="toast-progress-bar" x-show="show" x-transition:enter="progress-active">
                </div>
            </div>
        </div>
    </div>

    <style>
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 999999;
            pointer-events: none;
        }

        .custom-toast {
            pointer-events: auto;
            width: 350px;
            background: #ffffff !important;
            /* Fundo escuro sólido */
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            position: relative;
        }

        /* Cores de borda baseadas no tipo */
        .custom-toast.success {
            border-left: 6px solid #10b981;
        }

        .custom-toast.error {
            border-left: 6px solid #ef4444;
        }

        .custom-toast.warning {
            border-left: 6px solid #f59e0b;
        }

        .custom-toast.info {
            border-left: 6px solid #3b82f6;
        }

        .toast-content {
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .toast-icon-wrapper {
            background: white;
            border-radius: 8px;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            flex-shrink: 0;
        }

        .toast-icon-wrapper svg {
            width: 30px;
            height: 30px;
        }

        .toast-text {
            flex: 1;
        }

        .toast-title {
            color: black !important;
            font-weight: 800 !important;
            font-size: 16px !important;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .toast-message {
            color: black !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            line-height: 1.2;
        }

        .toast-close {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: black;
            padding: 5px 10px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .toast-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Barra de Progresso */
        .toast-progress-container {
            height: 5px;
            background: rgba(0, 0, 0, 0.3);
            width: 100%;
        }

        .toast-progress-bar {
            height: 100%;
            background: white;
            box-shadow: 0 0 10px white;
            width: 0%;
        }

        /* Animações Alpine */
        .toast-enter {
            transition: all 0.3s ease-out;
        }

        .toast-enter-start {
            transform: translateX(100%);
            opacity: 0;
        }

        .toast-enter-end {
            transform: translateX(0);
            opacity: 1;
        }

        .toast-leave {
            transition: all 0.3s ease-in;
            opacity: 0;
            transform: scale(0.9);
        }

        .progress-active {
            transition: width 5000ms linear;
            width: 100% !important;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</div>