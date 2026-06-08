<div x-data="{
    toasts: [],
    soundEnabled: true,
    addToast(detail) {
        let toast = detail;
        if (Array.isArray(detail)) {
            toast = detail[0];
        }
        if (!toast) return;

        const toastId = Date.now();
        this.toasts.push({ 
            id: toastId, 
            message: toast.message || '', 
            type: toast.type || 'success', 
            duration: toast.duration || 4000 
        })

        if (this.soundEnabled) {
            const audio = new Audio('/sounds/success.mp3')
            audio.play().catch(err => console.log('Audio play blocked:', err))
        }

        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== toastId)
        }, toast.duration ?? 4000)
    }
}" x-on:toast.window="addToast($event.detail[0] || $event.detail)" class="toast toast-top toast-end z-50">
    <template x-for="toast in toasts" :key="toast.id">
        <div class="alert shadow-lg border"
            :class="{
                'alert-success': toast.type === 'success',
                'alert-info': toast.type === 'info',
                'alert-warning': toast.type === 'warning',
                'alert-error': toast.type === 'error'
            }">
            <span x-text="toast.message"></span>
        </div>
    </template>
</div>
