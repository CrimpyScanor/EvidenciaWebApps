<template>
    <div class="dashboard-shell">
        <header class="topbar">
            <a class="dashboard-brand" href="/dashboard">
                <span class="brand-icon">H</span>
                <span>HALCON<small>OPERATIONS</small></span>
            </a>
            <div class="topbar-right" v-if="user">
                <div class="user-chip">
                    <span class="avatar">{{ initials(user.name) }}</span>
                    <span class="user-meta"><strong>{{ user.name }}</strong><small>{{ roleName(user.role) }}</small></span>
                </div>
                <button class="logout-button" @click="logout" :disabled="loggingOut">Cerrar sesión ↗</button>
            </div>
        </header>

        <main class="dashboard-main">
            <div v-if="loading" class="loading-state">Preparando tu espacio de trabajo...</div>
            <div v-else-if="pageError" class="alert-error">{{ pageError }}</div>
            <template v-else-if="user">
                <section class="welcome-row">
                    <div>
                        <p class="eyebrow">CENTRO DE OPERACIONES</p>
                        <h1>Hola, {{ firstName }}.</h1>
                        <p class="welcome-copy">Este es tu espacio de trabajo. Aquí tienes lo que necesitas para continuar.</p>
                    </div>
                    <div class="date-chip"><span>●</span> Sesión activa</div>
                </section>

                <nav class="workspace-nav" aria-label="Módulos">
                    <button v-for="tab in tabs" :key="tab.id" :class="{active: activeTab === tab.id}"
                            @click="activeTab = tab.id">{{ tab.label }}</button>
                </nav>

                <div v-if="notice" class="alert-success">{{ notice }}</div>
                <div v-if="actionError" class="alert-error">{{ actionError }}</div>

                <section v-if="activeTab === 'overview' && user.role === 'Administrator'" class="overview-grid">
                    <article class="metric-card">
                        <span class="metric-label">Empleados registrados</span>
                        <strong>{{ employees.length }}</strong>
                        <small>Personal con acceso al sistema</small>
                    </article>
                    <article class="metric-card">
                        <span class="metric-label">Empleados activos</span>
                        <strong>{{ employees.filter(e => e.is_active).length }}</strong>
                        <small>Cuentas habilitadas</small>
                    </article>
                    <article class="metric-card">
                        <span class="metric-label">Pedidos registrados</span>
                        <strong>{{ orders.length }}</strong>
                        <small>Operaciones visibles</small>
                    </article>
                    <article class="overview-note">
                        <span class="note-icon">✦</span>
                        <div><strong>Panel de administración</strong><p>Gestiona las cuentas del equipo, asigna roles y supervisa el flujo de pedidos desde un solo lugar.</p></div>
                    </article>
                </section>

                <section v-if="activeTab === 'employees' && user.role === 'Administrator'" class="content-panel">
                    <div class="section-heading">
                        <div><p class="eyebrow">CONTROL DE ACCESO</p><h2>Gestión de empleados</h2><p>Registra al personal y administra sus permisos dentro de HALCON.</p></div>
                        <button class="primary-action" @click="openCreate">＋ Nuevo empleado</button>
                    </div>

                    <form v-if="showEmployeeForm" class="editor-card" @submit.prevent="saveEmployee">
                        <div class="editor-heading">
                            <div><h3>{{ editingEmployee ? 'Editar empleado' : 'Registrar empleado' }}</h3><p>Los campos con * son obligatorios.</p></div>
                            <button type="button" class="close-button" @click="closeEmployeeForm">×</button>
                        </div>
                        <div class="form-grid">
                            <label>Nombre completo *<input v-model.trim="employeeForm.name" required maxlength="255" placeholder="Nombre del empleado" /></label>
                            <label>Correo electrónico *<input v-model.trim="employeeForm.email" type="email" required placeholder="empleado@halcon.local" /></label>
                            <label>Rol asignado *
                                <select v-model="employeeForm.role" required>
                                    <option v-for="role in roles" :key="role.value" :value="role.value">{{ role.label }}</option>
                                </select>
                            </label>
                            <label>{{ editingEmployee ? 'Nueva contraseña (opcional)' : 'Contraseña *' }}
                                <input v-model="employeeForm.password" type="password" :required="!editingEmployee" minlength="8" autocomplete="new-password" placeholder="Mínimo 8 caracteres" />
                            </label>
                            <label>Confirmar contraseña
                                <input v-model="employeeForm.password_confirmation" type="password" :required="!!employeeForm.password || !editingEmployee" minlength="8" autocomplete="new-password" placeholder="Repite la contraseña" />
                            </label>
                            <label class="check-field"><input v-model="employeeForm.is_active" type="checkbox" /> Cuenta activa</label>
                        </div>
                        <p v-if="formError" class="form-error">{{ formError }}</p>
                        <div class="editor-actions">
                            <button type="button" class="secondary-action" @click="closeEmployeeForm">Cancelar</button>
                            <button class="primary-action" type="submit" :disabled="saving">{{ saving ? 'Guardando...' : 'Guardar empleado' }}</button>
                        </div>
                    </form>

                    <div class="table-wrap">
                        <table class="data-table">
                            <thead><tr><th>Empleado</th><th>Rol</th><th>Estado</th><th>Acciones</th></tr></thead>
                            <tbody>
                                <tr v-for="employee in employees" :key="employee.id">
                                    <td><div class="employee-cell"><span class="avatar light">{{ initials(employee.name) }}</span><span><strong>{{ employee.name }}</strong><small>{{ employee.email }}</small></span></div></td>
                                    <td><span class="role-pill">{{ roleName(employee.role) }}</span></td>
                                    <td><span :class="['status-pill', employee.is_active ? 'enabled' : 'disabled']">{{ employee.is_active ? 'Activo' : 'Inactivo' }}</span></td>
                                    <td><div class="table-actions">
                                        <button class="text-action" @click="openEdit(employee)">Editar</button>
                                        <button class="text-action danger" :disabled="employee.id === user.id" @click="toggleEmployee(employee)">{{ employee.is_active ? 'Desactivar' : 'Activar' }}</button>
                                    </div></td>
                                </tr>
                                <tr v-if="!employees.length"><td colspan="4" class="empty-cell">Todavía no hay empleados registrados.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section v-if="activeTab === 'orders' || ['Sales','Purchasing','Warehouse','Route'].includes(user.role) && activeTab === 'work'" class="content-panel">
                    <div class="section-heading">
                        <div><p class="eyebrow">{{ moduleEyebrow }}</p><h2>{{ moduleTitle }}</h2><p>{{ moduleDescription }}</p></div>
                        <button v-if="canCreateOrders" class="primary-action" @click="openCreateOrder">＋ Nuevo pedido</button>
                    </div>

                    <form v-if="showOrderForm && canCreateOrders" class="editor-card" @submit.prevent="createOrder">
                        <div class="editor-heading"><div><h3>{{ editingOrder ? 'Editar pedido' : 'Registrar pedido' }}</h3><p>Ingresa la información del pedido.</p></div><button type="button" class="close-button" @click="closeOrderForm">×</button></div>
                        <div class="form-grid">
                            <label>Número de cliente *<input v-model.trim="orderForm.customer_number" required placeholder="Ej. C004" /></label>
                            <label>Número de factura *<input v-model.trim="orderForm.invoice_number" required placeholder="Ej. INV-1005" /></label>
                            <label>Producto *<input v-model.trim="orderForm.product_name" required placeholder="Nombre del producto" /></label>
                            <label>Fecha del pedido<input v-model="orderForm.order_date" type="date" /></label>
                            <label class="check-field"><input v-model="orderForm.purchase_required" type="checkbox" /> Requiere compra de material</label>
                        </div>
                        <p v-if="orderFormError" class="form-error">{{ orderFormError }}</p>
                        <div class="editor-actions"><button type="button" class="secondary-action" @click="closeOrderForm">Cancelar</button><button class="primary-action" type="submit" :disabled="savingOrder">{{ savingOrder ? 'Guardando...' : (editingOrder ? 'Guardar cambios' : 'Guardar pedido') }}</button></div>
                    </form>

                    <div class="order-list">
                        <article v-for="order in orders" :key="order.id" class="order-card">
                            <div class="order-main">
                                <div class="order-icon">▣</div>
                                <div class="order-info"><strong>{{ order.product_name }}</strong><span>{{ order.invoice_number }} · Cliente {{ order.customer_number }}</span><small>{{ formatDate(order.order_date) }}</small></div>
                            </div>
                            <div class="order-status-block"><span class="status-label">ESTADO</span><span :class="['order-status', statusClass(order.status)]">{{ statusName(order.status) }}</span></div>
                            <div class="order-actions">
                                <button v-if="canCreateOrders" class="text-action" @click="openEditOrder(order)">Editar</button>
                                <button v-if="user.role === 'Purchasing' && order.purchase_required" class="small-action" @click="updateOrder(order, 'purchase_complete')">Compra completada</button>
                                <button v-if="user.role === 'Warehouse' && ['Pending','Processing'].includes(order.status)" class="small-action" @click="updateOrder(order, 'warehouse_ready')">Preparado para ruta</button>
                                <button v-if="user.role === 'Route' && order.status === 'Ready for Route'" class="small-action" @click="updateOrder(order, 'dispatch')">Marcar enviado</button>
                                <template v-if="user.role === 'Route' && order.status === 'Shipped'">
                                    <input v-model="evidenceDrafts[order.id]" class="evidence-input" placeholder="Evidencia de entrega (folio o nota)" />
                                    <button class="small-action" @click="updateOrder(order, 'deliver')">Confirmar entrega</button>
                                </template>
                                <span v-if="order.delivery_evidence" class="evidence-note">Evidencia: {{ order.delivery_evidence }}</span>
                            </div>
                        </article>
                        <div v-if="!orders.length" class="empty-state"><span>✦</span><strong>No hay pedidos en esta bandeja</strong><p>Cuando haya operaciones asignadas a tu área, aparecerán aquí.</p></div>
                    </div>
                </section>
            </template>
        </main>
        <footer class="dashboard-footer">HALCON · ORDER MANAGEMENT SYSTEM <span>© 2026</span></footer>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue';

const user = ref(null);
const employees = ref([]);
const orders = ref([]);
const loading = ref(true);
const saving = ref(false);
const savingOrder = ref(false);
const loggingOut = ref(false);
const pageError = ref('');
const actionError = ref('');
const notice = ref('');
const formError = ref('');
const orderFormError = ref('');
const activeTab = ref('work');
const showEmployeeForm = ref(false);
const showOrderForm = ref(false);
const editingOrder = ref(null);
const editingEmployee = ref(null);
const evidenceDrafts = reactive({});

const roles = [
    { value: 'Administrator', label: 'Administrador' },
    { value: 'Sales', label: 'Ventas' },
    { value: 'Purchasing', label: 'Compras' },
    { value: 'Warehouse', label: 'Almacén' },
    { value: 'Route', label: 'Rutas y entregas' },
];

const employeeForm = reactive({ name: '', email: '', role: 'Sales', password: '', password_confirmation: '', is_active: true });
const orderForm = reactive({ customer_number: '', invoice_number: '', product_name: '', order_date: new Date().toISOString().slice(0, 10), purchase_required: false });

const firstName = computed(() => user.value?.name?.split(' ')[0] || 'equipo');
const canCreateOrders = computed(() => ['Administrator', 'Sales'].includes(user.value?.role));
const tabs = computed(() => {
    if (user.value?.role === 'Administrator') return [
        { id: 'overview', label: 'Resumen' },
        { id: 'employees', label: 'Empleados' },
        { id: 'orders', label: 'Pedidos' },
    ];
    return [{ id: 'work', label: moduleTitle.value }];
});
const moduleTitle = computed(() => ({
    Sales: 'Pedidos de clientes',
    Purchasing: 'Compras pendientes',
    Warehouse: 'Preparación de pedidos',
    Route: 'Rutas y entregas',
    Administrator: 'Pedidos',
}[user.value?.role] || 'Operaciones'));
const moduleEyebrow = computed(() => ({
    Sales: 'ÁREA COMERCIAL',
    Purchasing: 'ABASTECIMIENTO',
    Warehouse: 'ALMACÉN',
    Route: 'DISTRIBUCIÓN',
    Administrator: 'OPERACIÓN GENERAL',
}[user.value?.role] || 'OPERACIONES'));
const moduleDescription = computed(() => ({
    Sales: 'Registra nuevos pedidos y consulta su avance.',
    Purchasing: 'Consulta pedidos que requieren la compra de materiales y marca las compras completadas.',
    Warehouse: 'Prepara los pedidos procesados y envíalos al área de rutas.',
    Route: 'Gestiona los envíos y registra la evidencia de entrega.',
    Administrator: 'Consulta y supervisa los pedidos del sistema.',
}[user.value?.role] || ''));

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

async function api(url, options = {}) {
    const headers = { Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), ...(options.headers || {}) };
    if (options.body && !(options.body instanceof FormData)) headers['Content-Type'] = 'application/json';
    const response = await fetch(url, { credentials: 'same-origin', ...options, headers });
    const data = response.status === 204 ? {} : await response.json().catch(() => ({}));
    if (!response.ok) {
        const firstError = data.errors ? Object.values(data.errors).flat()[0] : null;
        throw new Error(firstError || data.message || 'Ocurrió un error al procesar la solicitud.');
    }
    return data;
}

async function loadWorkspace() {
    try {
        const data = await api('/api/me');
        user.value = data.user;
        activeTab.value = user.value.role === 'Administrator' ? 'overview' : 'work';
        await loadOrders();
        if (user.value.role === 'Administrator') await loadEmployees();
    } catch (error) {
        if (error.message.includes('Unauthenticated') || error.message.includes('Unauthenticated.')) {
            window.location.href = '/login';
            return;
        }
        pageError.value = error.message;
    } finally {
        loading.value = false;
    }
}

async function loadEmployees() {
    employees.value = await api('/api/employees');
}

async function loadOrders() {
    orders.value = await api('/api/workspace/orders');
}

function roleName(role) {
    return roles.find(item => item.value === role)?.label || role;
}
function initials(name = '') {
    return name.split(' ').filter(Boolean).slice(0, 2).map(part => part[0]).join('').toUpperCase();
}
function formatDate(value) {
    if (!value) return 'Fecha no especificada';
    const parsed = new Date(`${value}T00:00:00`);
    return Number.isNaN(parsed.getTime()) ? value : parsed.toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' });
}
function statusName(status) {
    return ({ Pending: 'Pendiente', Processing: 'En proceso', 'Ready for Route': 'Listo para ruta', Shipped: 'Enviado', Delivered: 'Entregado' })[status] || status;
}
function statusClass(status) {
    return ({ Pending: 'pending', Processing: 'processing', 'Ready for Route': 'ready', Shipped: 'shipped', Delivered: 'delivered' })[status] || 'pending';
}
function resetEmployeeForm() {
    Object.assign(employeeForm, { name: '', email: '', role: 'Sales', password: '', password_confirmation: '', is_active: true });
}
function openCreate() {
    editingEmployee.value = null;
    resetEmployeeForm();
    formError.value = '';
    showEmployeeForm.value = true;
}
function openEdit(employee) {
    editingEmployee.value = employee;
    Object.assign(employeeForm, { name: employee.name, email: employee.email, role: employee.role, password: '', password_confirmation: '', is_active: employee.is_active });
    formError.value = '';
    showEmployeeForm.value = true;
}
function closeEmployeeForm() {
    showEmployeeForm.value = false;
    editingEmployee.value = null;
    resetEmployeeForm();
}
async function saveEmployee() {
    saving.value = true;
    formError.value = '';
    actionError.value = '';
    try {
        const payload = { ...employeeForm };
        if (editingEmployee.value && !payload.password) {
            delete payload.password;
            delete payload.password_confirmation;
        }
        const url = editingEmployee.value ? `/api/employees/${editingEmployee.value.id}` : '/api/employees';
        await api(url, { method: editingEmployee.value ? 'PUT' : 'POST', body: JSON.stringify(payload) });
        await loadEmployees();
        const wasEditing = !!editingEmployee.value;
        closeEmployeeForm();
        notice.value = wasEditing ? 'Empleado actualizado correctamente.' : 'Empleado registrado correctamente.';
    } catch (error) {
        formError.value = error.message;
    } finally {
        saving.value = false;
    }
}
async function toggleEmployee(employee) {
    actionError.value = '';
    notice.value = '';
    try {
        await api(`/api/employees/${employee.id}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ is_active: !employee.is_active }),
        });
        await loadEmployees();
        notice.value = employee.is_active ? 'Cuenta desactivada.' : 'Cuenta activada.';
    } catch (error) {
        actionError.value = error.message;
    }
}
function openEditOrder(order) {
    editingOrder.value = order;
    Object.assign(orderForm, {
        customer_number: order.customer_number,
        invoice_number: order.invoice_number,
        product_name: order.product_name,
        order_date: order.order_date || '',
        purchase_required: order.purchase_required,
    });
    orderFormError.value = '';
    showOrderForm.value = true;
}
function openCreateOrder() {
    editingOrder.value = null;
    Object.assign(orderForm, { customer_number: '', invoice_number: '', product_name: '', order_date: new Date().toISOString().slice(0, 10), purchase_required: false });
    orderFormError.value = '';
    showOrderForm.value = true;
}
function closeOrderForm() {
    showOrderForm.value = false;
    editingOrder.value = null;
    orderFormError.value = '';
}
async function createOrder() {
    savingOrder.value = true;
    orderFormError.value = '';
    try {
        if (editingOrder.value) {
            await api(`/api/workspace/orders/${editingOrder.value.id}`, {
                method: 'PATCH',
                body: JSON.stringify({ action: 'sales_update', ...orderForm }),
            });
        } else {
            await api('/api/workspace/orders', { method: 'POST', body: JSON.stringify(orderForm) });
        }
        const wasEditing = !!editingOrder.value;
        editingOrder.value = null;
        Object.assign(orderForm, { customer_number: '', invoice_number: '', product_name: '', order_date: new Date().toISOString().slice(0, 10), purchase_required: false });
        showOrderForm.value = false;
        await loadOrders();
        notice.value = wasEditing ? 'Pedido actualizado correctamente.' : 'Pedido registrado correctamente.';
    } catch (error) {
        orderFormError.value = error.message;
    } finally {
        savingOrder.value = false;
    }
}
async function updateOrder(order, action) {
    actionError.value = '';
    notice.value = '';
    const payload = { action };
    if (action === 'deliver') payload.delivery_evidence = evidenceDrafts[order.id] || '';
    try {
        await api(`/api/workspace/orders/${order.id}`, { method: 'PATCH', body: JSON.stringify(payload) });
        await loadOrders();
        notice.value = 'Pedido actualizado correctamente.';
    } catch (error) {
        actionError.value = error.message;
    }
}
async function logout() {
    loggingOut.value = true;
    try {
        const data = await api('/logout', { method: 'POST' });
        window.location.href = data.redirect || '/login';
    } catch (error) {
        actionError.value = error.message;
        loggingOut.value = false;
    }
}

onMounted(loadWorkspace);
</script>

<style scoped>
.dashboard-shell{min-height:100vh;background:#f4f7fa;color:#1b3045;font-family:Arial,sans-serif;display:flex;flex-direction:column}
.topbar{height:76px;padding:0 clamp(22px,6vw,90px);background:#102b46;display:flex;align-items:center;justify-content:space-between;color:#fff}
.dashboard-brand{display:flex;align-items:center;gap:11px;color:#fff;text-decoration:none;font-size:16px;font-weight:900;letter-spacing:2px}
.dashboard-brand small{display:block;margin-top:4px;color:#b9c8d5;font-size:8px;letter-spacing:2.4px}
.brand-icon{width:39px;height:39px;display:grid;place-items:center;background:#e9a13a;color:#102b46;border-radius:11px;font-size:21px}
.topbar-right{display:flex;align-items:center;gap:24px}
.user-chip{display:flex;align-items:center;gap:10px}
.avatar{display:grid;place-items:center;width:38px;height:38px;flex:none;border-radius:50%;background:#e9a13a;color:#102b46;font-size:12px;font-weight:800}
.avatar.light{background:#e9eff5;color:#34516d}
.user-meta strong,.user-meta small{display:block}.user-meta strong{font-size:12px}.user-meta small{margin-top:4px;color:#c0ced9;font-size:10px}
.logout-button{border:1px solid #ffffff44;background:transparent;color:#e7edf3;border-radius:7px;padding:10px 13px;font-size:11px;cursor:pointer}
.logout-button:hover{background:#ffffff12}.logout-button:disabled{opacity:.5}
.dashboard-main{width:min(1160px,calc(100% - 44px));margin:0 auto;flex:1;padding:48px 0 70px}
.loading-state,.empty-state{text-align:center;padding:75px 20px;color:#78899a}
.welcome-row{display:flex;justify-content:space-between;align-items:flex-end;gap:20px;margin-bottom:32px}
.eyebrow{font-size:10px;letter-spacing:2px;color:#b77926;font-weight:800;margin:0 0 10px}
.welcome-row h1{font-size:35px;letter-spacing:-1.2px;margin:0;color:#152f49}
.welcome-copy{margin:10px 0 0;color:#718194;font-size:13px}
.date-chip{padding:10px 13px;border-radius:20px;background:#e7f3ed;color:#3b8061;font-size:11px;font-weight:700;white-space:nowrap}
.date-chip span{font-size:9px;margin-right:6px}
.workspace-nav{display:flex;gap:7px;border-bottom:1px solid #dfe6ed;margin-bottom:25px}
.workspace-nav button{padding:13px 18px;border:0;border-bottom:2px solid transparent;background:transparent;color:#758597;font-size:12px;font-weight:700;cursor:pointer}
.workspace-nav button.active{color:#183b5b;border-color:#e9a13a}
.overview-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.metric-card,.overview-note,.content-panel{background:#fff;border:1px solid #e5ebf0;border-radius:13px;box-shadow:0 5px 18px #1b304508}
.metric-card{padding:23px}
.metric-label{display:block;color:#718194;font-size:11px;font-weight:700}
.metric-card strong{display:block;margin:13px 0 8px;font-size:35px;color:#163754}
.metric-card small{font-size:11px;color:#9aa6b2}
.overview-note{grid-column:1/-1;display:flex;align-items:center;gap:17px;padding:23px}
.note-icon{width:43px;height:43px;display:grid;place-items:center;border-radius:12px;background:#fff5e6;color:#bd7b20;font-size:21px}
.overview-note strong{font-size:13px}.overview-note p{margin:6px 0 0;color:#718194;font-size:12px;line-height:1.5}
.content-panel{padding:27px}
.section-heading{display:flex;justify-content:space-between;align-items:center;gap:20px;margin-bottom:22px}
.section-heading h2{margin:0;color:#163754;font-size:23px;letter-spacing:-.5px}
.section-heading p:not(.eyebrow){margin:7px 0 0;color:#8190a0;font-size:12px}
.primary-action,.small-action{border:0;border-radius:7px;background:#e9a13a;color:#142f49;padding:11px 15px;font-size:11px;font-weight:800;cursor:pointer}
.primary-action:hover,.small-action:hover{background:#f0b75f}
.primary-action:disabled{opacity:.6;cursor:wait}
.editor-card{padding:21px;margin:0 0 22px;background:#f8fafc;border:1px solid #e2e9ef;border-radius:10px}
.editor-heading{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:18px}
.editor-heading h3{margin:0;font-size:15px;color:#1c3c59}.editor-heading p{margin:5px 0 0;color:#8291a0;font-size:11px}
.close-button{border:0;background:transparent;color:#8291a0;font-size:24px;cursor:pointer}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.form-grid label{display:flex;flex-direction:column;gap:7px;color:#52677b;font-size:11px;font-weight:700}
.form-grid input:not([type=checkbox]),.form-grid select,.evidence-input{width:100%;box-sizing:border-box;border:1px solid #dce4eb;border-radius:7px;padding:11px 12px;background:#fff;color:#263c51;font-size:12px;outline:none}
.form-grid input:focus,.form-grid select:focus,.evidence-input:focus{border-color:#d69a42}
.check-field{flex-direction:row!important;align-items:center;gap:9px!important;padding-top:22px}
.check-field input{accent-color:#d99a3c}
.editor-actions{display:flex;justify-content:flex-end;gap:9px;margin-top:18px}
.secondary-action{border:1px solid #d9e1e8;border-radius:7px;background:#fff;color:#607386;padding:10px 14px;font-size:11px;cursor:pointer}
.form-error{color:#b53e38;font-size:11px;margin:12px 0 0}
.table-wrap{overflow-x:auto}
.data-table{width:100%;border-collapse:collapse;text-align:left;white-space:nowrap}
.data-table th{padding:12px 13px;background:#f7f9fb;color:#8a98a6;font-size:9px;letter-spacing:1px;text-transform:uppercase}
.data-table td{padding:14px 13px;border-bottom:1px solid #edf1f4;font-size:11px}
.employee-cell{display:flex;align-items:center;gap:10px}.employee-cell strong,.employee-cell small{display:block}.employee-cell strong{font-size:11px;color:#29435b}.employee-cell small{margin-top:4px;color:#8a98a6;font-size:10px}
.role-pill{display:inline-block;border-radius:20px;padding:6px 9px;background:#edf3f8;color:#49657e;font-size:10px}
.status-pill{display:inline-block;border-radius:20px;padding:6px 9px;font-size:10px;font-weight:700}
.status-pill.enabled{background:#e8f5ed;color:#3b805c}.status-pill.disabled{background:#fceeed;color:#b35c53}
.table-actions{display:flex;gap:13px}.text-action{border:0;background:transparent;color:#37668c;font-size:10px;font-weight:700;cursor:pointer}.text-action.danger{color:#b35c53}.text-action:disabled{opacity:.3;cursor:not-allowed}
.empty-cell{text-align:center;color:#94a0ac;padding:30px!important}
.order-list{display:flex;flex-direction:column;gap:10px}
.order-card{display:grid;grid-template-columns:minmax(220px,1.5fr) minmax(125px,.6fr) minmax(190px,1fr);align-items:center;gap:16px;padding:16px;border:1px solid #e8edf1;border-radius:10px;background:#fff}
.order-main{display:flex;align-items:center;gap:12px;min-width:0}
.order-icon{display:grid;place-items:center;width:39px;height:39px;border-radius:10px;background:#edf3f8;color:#365c7c;font-size:19px;flex:none}
.order-info{min-width:0}.order-info strong,.order-info span,.order-info small{display:block;overflow:hidden;text-overflow:ellipsis}
.order-info strong{font-size:12px;color:#29435b}.order-info span{margin-top:5px;color:#748598;font-size:10px}.order-info small{margin-top:4px;color:#a1acb7;font-size:9px}
.status-label{display:block;color:#a1acb7;font-size:8px;letter-spacing:1px;margin-bottom:6px}
.order-status{display:inline-block;padding:6px 9px;border-radius:20px;font-size:9px;font-weight:700}
.order-status.pending{background:#fff4df;color:#a66d1e}.order-status.processing{background:#eaf1fb;color:#4775a6}.order-status.ready{background:#f0eafd;color:#7959a6}.order-status.shipped{background:#e7f3f5;color:#3f7d88}.order-status.delivered{background:#e8f5ed;color:#3b805c}
.order-actions{display:flex;align-items:center;justify-content:flex-end;gap:7px;flex-wrap:wrap}
.small-action{padding:8px 10px;font-size:9px;white-space:nowrap}
.evidence-input{max-width:170px;padding:8px;font-size:10px}
.evidence-note{font-size:9px;color:#718194;max-width:100%;overflow-wrap:anywhere}
.empty-state{border:1px dashed #d9e2e9;border-radius:10px;background:#fbfcfd;padding:55px 20px}
.empty-state span{display:block;color:#d69a42;font-size:24px;margin-bottom:10px}.empty-state strong{color:#52677b;font-size:13px}.empty-state p{font-size:11px}
.alert-success,.alert-error{padding:11px 14px;margin-bottom:17px;border-radius:8px;font-size:11px}
.alert-success{background:#eaf6ee;color:#397a58}.alert-error{background:#fff0ef;color:#b53e38}
.dashboard-footer{display:flex;justify-content:space-between;padding:20px clamp(22px,6vw,90px);border-top:1px solid #e4eaf0;color:#a0abb5;font-size:9px;letter-spacing:1.2px}
@media(max-width:850px){.overview-grid{grid-template-columns:repeat(2,1fr)}.order-card{grid-template-columns:1fr 1fr}.order-actions{grid-column:1/-1;justify-content:flex-start}}
@media(max-width:600px){.topbar{height:auto;min-height:70px;padding:12px 18px}.topbar-right{gap:10px}.user-meta{display:none}.logout-button{padding:9px;font-size:10px}.dashboard-main{width:calc(100% - 28px);padding:32px 0 48px}.welcome-row{align-items:flex-start;flex-direction:column}.welcome-row h1{font-size:29px}.workspace-nav button{padding:12px 10px;font-size:10px}.overview-grid{grid-template-columns:1fr}.overview-note{grid-column:auto}.content-panel{padding:17px}.section-heading{align-items:flex-start;flex-direction:column}.form-grid{grid-template-columns:1fr}.check-field{padding-top:4px}.order-card{grid-template-columns:1fr}.order-actions{grid-column:auto;justify-content:flex-start}.order-status-block{padding-left:51px}.dashboard-footer{padding:18px;font-size:8px}}
</style>
