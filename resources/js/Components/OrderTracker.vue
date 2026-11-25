
<template>
    <div class="page">
        <header class="header">
            <h1>HALCON</h1>
            <div class="header-actions">
                <span>ORDER TRACKING SYSTEM</span>
                <a href="/login">Acceso empleados →</a>
            </div>
        </header>

        <main class="container">
            <section class="tracking-card">
                <div class="icon">📦</div>

                <h2>Track Your Order</h2>

                <p class="description">
                    Enter your customer number and invoice number
                    to check the current status of your order.
                </p>

                <form @submit.prevent="checkOrder">
                    <div class="form-group">
                        <label for="customer">
                            Customer Number
                        </label>

                        <input
                            id="customer"
                            v-model="customerNumber"
                            type="text"
                            placeholder="Example: C001"
                            required
                        />
                    </div>

                    <div class="form-group">
                        <label for="invoice">
                            Invoice Number
                        </label>

                        <input
                            id="invoice"
                            v-model="invoiceNumber"
                            type="text"
                            placeholder="Example: INV-1001"
                            required
                        />
                    </div>

                    <button type="submit" :disabled="loading">
                    {{ loading ? 'Searching...' : 'Check Order Status' }}
                    </button>
                </form>

                
                <div v-if="loading" class="message">
                    Searching for your order...
                </div>

                <div v-if="errorMessage" class="error-message">
                    {{ errorMessage }}
                </div>

                <div v-if="order" class="order-result">
                    <h3>Order Information</h3>

                    <div class="order-detail">
                        <span>Customer Number</span>
                        <strong>{{ order.customer_number }}</strong>
                    </div>

                    <div class="order-detail">
                        <span>Invoice Number</span>
                        <strong>{{ order.invoice_number }}</strong>
                    </div>

                    <div class="order-detail">
                        <span>Product</span>
                        <strong>{{ order.product_name }}</strong>
                    </div>

                    <div class="order-detail">
                        <span>Order Date</span>
                        <strong>{{ order.order_date }}</strong>
                    </div>

                    <div class="order-detail">
                        <span>Status</span>
                        <strong class="status">{{ order.status }}</strong>
                    </div>
                </div>

            </section>
        </main>

        <footer>
            © 2026 HALCON. All rights reserved.
        </footer>
    </div>
</template>

<script setup>

import { ref } from 'vue';

const customerNumber = ref('');
const invoiceNumber = ref('');

const order = ref(null);
const errorMessage = ref('');
const loading = ref(false);

async function checkOrder() {
    order.value = null;
    errorMessage.value = '';
    loading.value = true;

    try {
        const params = new URLSearchParams({
            customer_number: customerNumber.value.trim(),
            invoice_number: invoiceNumber.value.trim()
        });

        const response = await fetch(
            `/api/orders?${params.toString()}`
        );

        const data = await response.json();

        if (!response.ok) {
            throw new Error(
                data.message || 'Unable to find your order.'
            );
        }

        order.value = data.order;

    } catch (error) {
        errorMessage.value = error.message;

    } finally {
        loading.value = false;
    }
}

</script>

<style scoped>
.page {
    min-height: 100vh;
    background: #f3f6fb;
    font-family: Arial, sans-serif;
    color: #1e293b;
}

.header {
    background: #102b46;
    color: white;
    padding: 25px 8%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.header h1 {
    margin: 0;
    letter-spacing: 3px;
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 24px;
}
.header-actions span {
    font-size: 12px;
    letter-spacing: 2px;
    color: #cbd5e1;
}
.header-actions a {
    color: #f3b75e;
    font-size: 12px;
    font-weight: bold;
    text-decoration: none;
}
.header-actions a:hover {
    color: white;
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 70px 20px;
}

.tracking-card {
    background: white;
    width: 100%;
    max-width: 480px;
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
    text-align: center;
}

.icon {
    font-size: 45px;
}

.tracking-card h2 {
    font-size: 28px;
    margin-bottom: 12px;
    color: #102b46;
}

.description {
    color: #64748b;
    line-height: 1.6;
    margin-bottom: 30px;
}

.form-group {
    text-align: left;
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-weight: bold;
    margin-bottom: 8px;
}

.form-group input {
    width: 100%;
    padding: 13px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    box-sizing: border-box;
    font-size: 15px;
}

.form-group input:focus {
    outline: none;
    border-color: #2563eb;
}

button {
    width: 100%;
    padding: 15px;
    background: #e99b32;
    color: #102b46;
    border: none;
    border-radius: 8px;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    background: #d88922;
}

.message {
    color: #64748b;
    margin-top: 20px;
}

footer {
    text-align: center;
    padding: 25px;
    color: #64748b;
    font-size: 13px;
}

.error-message {
    margin-top: 20px;
    padding: 12px;
    background: #fee2e2;
    color: #b91c1c;
    border-radius: 8px;
}

.order-result {
    margin-top: 25px;
    padding: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    text-align: left;
}

.order-result h3 {
    color: #102b46;
    margin-top: 0;
    margin-bottom: 20px;
}

.order-detail {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 0;
    border-bottom: 1px solid #e2e8f0;
}

.order-detail span {
    color: #64748b;
}

.order-detail strong {
    text-align: right;
}

.status {
    color: #2563eb;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

</style>
