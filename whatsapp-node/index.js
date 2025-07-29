const express = require('express');
const { Client, LocalAuth } = require('whatsapp-web.js');
const qrcode = require('qrcode-terminal');
const cors = require('cors');

const app = express();

// Enable CORS to allow requests from other origins (e.g., your Laravel app)
app.use(cors());

// Middleware to parse JSON bodies in incoming requests
app.use(express.json());

// Initialize WhatsApp client with local authentication to persist sessions
const client = new Client({
    authStrategy: new LocalAuth(),
    puppeteer: {
        executablePath: '/snap/bin/chromium', // Path to Chromium browser
        headless: true,                       // Run Chromium without GUI
        args: ['--no-sandbox', '--disable-setuid-sandbox'] // Necessary flags for some environments
    }
});

// Listen for QR code event to authenticate WhatsApp Web session
client.on('qr', (qr) => {
    console.log('📲 Scan this QR code with your WhatsApp mobile app:');
    qrcode.generate(qr, { small: true }); // Generate QR code in terminal
});

// Once the WhatsApp client is ready, log it
client.on('ready', () => {
    console.log('✅ WhatsApp client is ready');
});

// Initialize the client (starts Puppeteer and WhatsApp connection)
client.initialize();

/**
 * POST /send
 * API endpoint to send WhatsApp messages
 * Expects JSON body with:
 *  - number: phone number (string, without country code suffix)
 *  - message: message text (string)
 */
app.post('/send', async (req, res) => {
    const { number, message } = req.body;

    // Validate input
    if (!number || !message) {
        return res.status(400).json({ success: false, status: 'Phone number and message are required' });
    }

    try {
        // Format the number to WhatsApp ID format (number + '@c.us')
        const fullNumber = number + '@c.us';

        // Check if the number is registered on WhatsApp
        const isRegistered = await client.isRegisteredUser(fullNumber);

        if (!isRegistered) {
            // If number is not registered on WhatsApp, respond accordingly
            return res.status(400).json({ success: false, status: 'The number does not have WhatsApp' });
        }

        // Send the WhatsApp message
        await client.sendMessage(fullNumber, message);

        // Respond with success
        return res.json({ success: true, status: 'Message sent successfully' });

    } catch (err) {
        // Log any error that happens during sending
        console.error('❌ Error while sending message:', err);

        // Respond with failure message
        return res.status(500).json({ success: false, status: 'Failed to send message' });
    }
});

// Start the Express server on port 3000
app.listen(3000, () => {
    console.log('🚀 Server running on http://localhost:3000');
});
