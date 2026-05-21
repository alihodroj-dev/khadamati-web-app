<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stripe Payment Test — Khadamati</title>
    <script src="https://js.stripe.com/v3/"></script>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; display: flex; justify-content: center; padding: 40px 16px; }
        .card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); padding: 32px; width: 100%; max-width: 520px; }
        h1 { font-size: 1.3rem; color: #1a1a1a; margin-bottom: 4px; }
        .subtitle { color: #666; font-size: .875rem; margin-bottom: 24px; }
        #start-btn { background: #635bff; color: #fff; border: none; border-radius: 8px; padding: 12px 24px; font-size: 1rem; cursor: pointer; width: 100%; }
        #start-btn:hover { background: #4f46e5; }
        #start-btn:disabled { background: #aaa; cursor: not-allowed; }
        #log { margin: 20px 0; }
        .log-line { padding: 6px 10px; border-radius: 6px; font-size: .875rem; margin-bottom: 6px; }
        .log-info    { background: #f0f4ff; color: #333; }
        .log-success { background: #e6f9f0; color: #166534; font-weight: 500; }
        .log-error   { background: #fef2f2; color: #991b1b; font-weight: 500; }
        #payment-section { display: none; border-top: 1px solid #eee; padding-top: 24px; margin-top: 8px; }
        #payment-section h2 { font-size: 1rem; margin-bottom: 16px; color: #333; }
        .test-card-hint { background: #fffbeb; border: 1px solid #fbbf24; border-radius: 8px; padding: 12px; font-size: .8rem; color: #78350f; margin-bottom: 16px; }
        .test-card-hint strong { display: block; margin-bottom: 4px; }
        #card-element { border: 1px solid #d1d5db; border-radius: 8px; padding: 14px; background: #fafafa; margin-bottom: 16px; }
        #pay-btn { background: #16a34a; color: #fff; border: none; border-radius: 8px; padding: 12px 24px; font-size: 1rem; cursor: pointer; width: 100%; }
        #pay-btn:hover { background: #15803d; }
        #pay-btn:disabled { background: #aaa; cursor: not-allowed; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: .75rem; font-weight: 600; }
        .badge-pending { background: #fef9c3; color: #713f12; }
        .badge-paid    { background: #dcfce7; color: #166534; }
    </style>
</head>
<body>
<div class="card">
    <h1>Stripe Payment Test</h1>
    <p class="subtitle">Sandbox mode — no real money is charged</p>

    <button id="start-btn">▶ Run Full Payment Flow</button>

    <div id="log"></div>

    <div id="payment-section">
        <h2>Enter Test Card</h2>
        <div class="test-card-hint">
            <strong>Use Stripe's test card:</strong>
            Card number: <code>4242 4242 4242 4242</code><br>
            Expiry: any future date &nbsp;|&nbsp; CVC: any 3 digits
        </div>
        <div id="card-element"></div>
        <button id="pay-btn">Pay Now</button>
    </div>
</div>

<script>
    const BASE = '/api';
    const STRIPE_KEY = '{{ $stripe_key }}';

    function log(msg, type = 'info') {
        const div = document.createElement('div');
        div.className = 'log-line log-' + type;
        div.textContent = msg;
        document.getElementById('log').appendChild(div);
        console.log('[' + type + '] ' + msg);
    }

    async function api(method, path, body, token) {
        const headers = { 'Content-Type': 'application/json', 'Accept': 'application/json' };
        if (token) headers['Authorization'] = 'Bearer ' + token;
        const opts = { method, headers };
        if (body) opts.body = JSON.stringify(body);
        const res = await fetch(BASE + path, opts);
        return res.json();
    }

    document.getElementById('start-btn').addEventListener('click', async () => {
        document.getElementById('start-btn').disabled = true;
        document.getElementById('log').innerHTML = '';

        try {
            // 1. Register test citizen
            log('Step 1 — Registering test citizen...');
            const email = 'test_' + Date.now() + '@example.com';
            const reg = await api('POST', '/register', {
                name: 'Test Citizen',
                email,
                password: 'password123',
                national_id: 'TEST' + Date.now()
            });
            if (!reg.data?.token) throw new Error('Registration failed: ' + JSON.stringify(reg));
            const token = reg.data.token;
            log('✅ Registered: ' + email, 'success');

            // 2. Find a paid service
            log('Step 2 — Finding a paid service...');
            const servicesRes = await api('GET', '/services');
            const services = servicesRes.data?.services || [];
            const paid = services.filter(s => parseFloat(s.base_fee) > 0);
            if (!paid.length) throw new Error('No paid services found — run: php artisan db:seed');
            const service = paid[0];
            log('✅ Service: ' + service.name + ' ($' + service.base_fee + ')', 'success');

            // 3. Create service request
            log('Step 3 — Creating service request...');
            const srRes = await api('POST', '/service-requests',
                { service_id: service.id, citizen_notes: 'Stripe sandbox test' }, token);
            const srId = srRes.data?.service_request?.id ?? srRes.data?.id;
            if (!srId) throw new Error('Service request failed: ' + JSON.stringify(srRes));
            log('✅ Service request #' + srId + ' created', 'success');

            // 4. Create card payment
            log('Step 4 — Creating card payment...');
            const payRes = await api('POST', '/payments',
                { service_request_id: srId, payment_method: 'card' }, token);
            if (!payRes.data?.id) throw new Error('Payment failed: ' + JSON.stringify(payRes));
            const paymentId = payRes.data.id;
            log('✅ Payment #' + paymentId + ' created ($' + payRes.data.amount + ')', 'success');

            // 5. Create Stripe intent
            log('Step 5 — Creating Stripe PaymentIntent...');
            const intentRes = await api('POST', '/payments/' + paymentId + '/stripe/intent', null, token);
            if (!intentRes.data?.client_secret) throw new Error('Stripe intent failed: ' + JSON.stringify(intentRes));
            const clientSecret = intentRes.data.client_secret;
            const publishableKey = intentRes.data.publishable_key;
            log('✅ PaymentIntent created — showing card form', 'success');

            // 6. Show Stripe card form
            const stripe = Stripe(publishableKey);
            const elements = stripe.elements();
            const cardEl = elements.create('card', { style: { base: { fontSize: '16px' } } });
            document.getElementById('payment-section').style.display = 'block';
            cardEl.mount('#card-element');

            document.getElementById('pay-btn').addEventListener('click', async () => {
                document.getElementById('pay-btn').disabled = true;
                log('Confirming payment with Stripe...', 'info');

                const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                    payment_method: { card: cardEl }
                });

                if (error) {
                    log('❌ ' + error.message, 'error');
                    document.getElementById('pay-btn').disabled = false;
                } else if (paymentIntent.status === 'succeeded') {
                    log('🎉 Payment succeeded! Stripe status: ' + paymentIntent.status, 'success');
                    log('Payment ID in DB: #' + paymentId + ' — if you set up the Stripe CLI webhook, the DB status will update to "paid" automatically.', 'info');
                }
            });

        } catch (e) {
            log('❌ ' + e.message, 'error');
            document.getElementById('start-btn').disabled = false;
        }
    });
</script>
</body>
</html>
