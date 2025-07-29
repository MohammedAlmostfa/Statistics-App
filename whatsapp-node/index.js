const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const cors = require('cors');

const app = express();

app.use(cors());
app.use(express.json());

const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        executablePath: '/snap/bin/chromium',
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    }
});

client.on('qr', (qr) => {
    console.log('📲 Scan this QR code with your WhatsApp mobile app:');
    qrcode.generate(qr, { small: true });
});

client.on('ready', () => {
    console.log('✅ WhatsApp client is ready');
});

client.initialize();

// إعداد الحد اليومي وعداد الإرسال
const DAILY_LIMIT = 1;
let sentCount = 0;
let currentDate = new Date().toISOString().slice(0, 10); // yyyy-mm-dd

// وظيفة لتحديث العداد إذا تغير اليوم
function resetCounterIfNeeded() {
    const today = new Date().toISOString().slice(0, 10);
    if (today !== currentDate) {
        currentDate = today;
        sentCount = 0;
        console.log('🔄 Reset daily message counter for new day:', currentDate);
    }
}

app.post('/send', async (req, res) => {
    resetCounterIfNeeded();

    if (sentCount >= DAILY_LIMIT) {
        return res.status(429).json({
            success: false,
            status: 'تم استهلاك رصيد الرسائل اليومي، يرجى المحاولة غدًا.'
        });
    }

    const { number, message } = req.body;

    if (!number || !message) {
        return res.status(400).json({ success: false, status: 'رقم الهاتف والرسالة مطلوبان' });
    }

    try {
        const fullNumber = number + '@c.us';

        const isRegistered = await client.isRegisteredUser(fullNumber);

        if (!isRegistered) {
            return res.status(400).json({ success: false, status: 'الرقم غير مسجل في واتساب' });
        }

        await client.sendMessage(fullNumber, message);

        sentCount++; // زيادة العداد بعد الإرسال الناجح
        console.log(`✅ Message sent. Total sent today: ${sentCount}/${DAILY_LIMIT}`);

        return res.json({ success: true, status: 'تم إرسال الرسالة بنجاح' });

    } catch (err) {
        console.error('❌ Error while sending message:', err);
        return res.status(500).json({ success: false, status: 'فشل في إرسال الرسالة' });
    }
});

app.listen(3000, () => {
    console.log('🚀 Server running on http://localhost:3000');
});
