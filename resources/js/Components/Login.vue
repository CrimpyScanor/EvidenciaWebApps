<template>
    <main class="auth-page">
        <section class="auth-panel">
            <a class="brand-mark" href="/" aria-label="HALCON inicio">H</a>
            <p class="eyebrow">HALCON · SISTEMA INTERNO</p>
            <h1>Bienvenido de vuelta</h1>
            <p class="auth-copy">Inicia sesión con las credenciales que te proporcionó el administrador.</p>

            <form class="auth-form" @submit.prevent="login">
                <label for="email">Correo electrónico</label>
                <input id="email" v-model.trim="email" type="email" autocomplete="username"
                       placeholder="empleado@halcon.local" required />

                <label for="password">Contraseña</label>
                <input id="password" v-model="password" type="password" autocomplete="current-password"
                       placeholder="Tu contraseña" required />

                <label class="remember-option">
                    <input v-model="remember" type="checkbox" />
                    <span>Mantener mi sesión iniciada</span>
                </label>

                <p v-if="error" class="form-error" role="alert">{{ error }}</p>
                <button class="primary-button" type="submit" :disabled="loading">
                    {{ loading ? 'Validando...' : 'Iniciar sesión' }}
                    <span aria-hidden="true">→</span>
                </button>
            </form>

            <a class="back-link" href="/">← Volver al rastreador de pedidos</a>
            <p class="auth-footnote">Acceso exclusivo para personal autorizado.</p>
        </section>
        <aside class="auth-aside">
            <div class="aside-orbit orbit-one"></div>
            <div class="aside-orbit orbit-two"></div>
            <div class="aside-content">
                <span class="aside-kicker">CONTROL · OPERACIÓN · ENTREGA</span>
                <div class="hawk-symbol">✦</div>
                <h2>Todo el flujo.<br />En una sola vista.</h2>
                <p>Coordina equipos, administra pedidos y mantén cada operación en movimiento.</p>
                <div class="aside-line"></div>
                <span class="aside-caption">HALCON ORDER MANAGEMENT</span>
            </div>
        </aside>
    </main>
</template>

<script setup>
import { ref } from 'vue';

const email = ref('');
const password = ref('');
const remember = ref(false);
const loading = ref(false);
const error = ref('');

async function login() {
    loading.value = true;
    error.value = '';

    try {
        const response = await fetch('/login', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                email: email.value,
                password: password.value,
                remember: remember.value,
            }),
        });

        const data = await response.json();
        if (!response.ok) {
            throw new Error(data.errors?.email?.[0] || data.message || 'No fue posible iniciar sesión.');
        }

        window.location.href = data.redirect || '/dashboard';
    } catch (err) {
        error.value = err.message || 'Ocurrió un error al iniciar sesión.';
    } finally {
        loading.value = false;
    }
}
</script>

<style scoped>
.auth-page{min-height:100vh;display:grid;grid-template-columns:minmax(420px, .92fr) 1.08fr;background:#f6f8fb;color:#14263a;font-family:Arial,sans-serif}
.auth-panel{width:min(440px,calc(100% - 48px));margin:auto;padding:56px 0}
.brand-mark{display:grid;place-items:center;width:48px;height:48px;border-radius:14px;background:#142f4b;color:#f2a63b;text-decoration:none;font-size:25px;font-weight:900;box-shadow:0 8px 20px #142f4b22}
.eyebrow{margin:38px 0 12px;color:#b77926;font-size:11px;font-weight:800;letter-spacing:2px}
h1{margin:0;font-size:34px;letter-spacing:-1.2px;color:#142f4b}
.auth-copy{margin:14px 0 32px;color:#6d7b8c;line-height:1.65;font-size:14px}
.auth-form{display:flex;flex-direction:column;gap:10px}
.auth-form>label{font-size:13px;font-weight:700;color:#33465a;margin-top:8px}
.auth-form input:not([type=checkbox]){width:100%;box-sizing:border-box;padding:14px 15px;border:1px solid #dce3eb;border-radius:9px;background:#fff;font-size:14px;outline:none}
.auth-form input:focus{border-color:#d99a3c;box-shadow:0 0 0 3px #d99a3c22}
.remember-option{display:flex;align-items:center;gap:9px;color:#6d7b8c!important;font-weight:500!important;margin:8px 0 12px!important}
.remember-option input{accent-color:#d99a3c}
.primary-button{display:flex;justify-content:space-between;align-items:center;border:0;border-radius:9px;padding:15px 17px;background:#e9a13a;color:#152d46;font-weight:800;font-size:14px;cursor:pointer}
.primary-button:disabled{opacity:.65;cursor:wait}
.form-error{margin:0;padding:11px 13px;border-radius:8px;background:#fff0ef;color:#b53e38;font-size:13px}
.back-link{display:inline-block;margin-top:25px;color:#52677c;text-decoration:none;font-size:13px}
.back-link:hover{color:#b77926}
.auth-footnote{margin-top:54px;color:#9aa5b1;font-size:11px}
.auth-aside{position:relative;overflow:hidden;display:flex;align-items:center;justify-content:center;background:linear-gradient(145deg,#102942,#183d5e 60%,#10243a);color:white;padding:50px}
.aside-content{position:relative;z-index:1;max-width:470px}
.aside-kicker,.aside-caption{font-size:10px;letter-spacing:2.5px;color:#d7a452;font-weight:700}
.hawk-symbol{display:grid;place-items:center;width:88px;height:88px;margin:48px 0 30px;border:1px solid #d7a45277;border-radius:50%;color:#efb75e;font-size:43px}
.aside-content h2{font-size:48px;line-height:1.08;letter-spacing:-1.8px;margin:0 0 20px}
.aside-content p{max-width:370px;color:#c3d0dc;line-height:1.7;font-size:15px}
.aside-line{height:1px;width:64px;background:#d7a452;margin:40px 0 18px}
.aside-orbit{position:absolute;border:1px solid #ffffff12;border-radius:50%;width:650px;height:650px;right:-290px;top:-160px}
.orbit-two{width:900px;height:900px;right:-400px;top:-280px}
@media(max-width:850px){.auth-page{grid-template-columns:1fr}.auth-aside{display:none}.auth-panel{padding:42px 0}.auth-footnote{margin-top:35px}}
</style>
