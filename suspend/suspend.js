const mysql = require('mysql2/promise');
const axios = require('axios');
const cron = require('node-cron');

// --- Configuration ---
const DB_CONFIG = {
    host: '127.0.0.1', // Your database host
    user: 'pterodactyl1', // Database username
    password: 'admin1234', // Database password
    database: 'panel1' // Pterodactyl database name
};

const PTERO_URL = 'https://panel.kesor.cam';
const PTERO_API_KEY = 'ptla_MzjSaPYpRHApU6c8vd6j314bkAN7KBULTm1qZ5NyGdJ'; // Must be an Application API Key

async function checkAndSuspendServers() {
    let connection;
    try {
        connection = await mysql.createConnection(DB_CONFIG);

        // This query assumes you have added a custom 'due_time' column to your servers table.
        // It selects servers where the due_time is in the past and they aren't already suspended.
        // If you base it purely on creation date being 30 days old, you would use:
        // WHERE created_at <= DATE_SUB(NOW(), INTERVAL 30 DAY)
        const [rows] = await connection.execute(`
            SELECT id, uuid, name
            FROM servers
            WHERE due_time <= NOW()
            AND suspended = 0
        `);

        console.log(`[System] Found ${rows.length} servers past their due date.`);

        for (const server of rows) {
            try {
                // Suspend the server safely via the Pterodactyl Application API
                await axios.post(`${PTERO_URL}/api/application/servers/${server.id}/suspend`, null, {
                    headers: {
                        'Authorization': `Bearer ${PTERO_API_KEY}`,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                });

                console.log(`[Success] Suspended server: ${server.name} (ID: ${server.id})`);
            } catch (apiError) {
                console.error(`[Error] Failed to suspend ${server.name} (ID: ${server.id}):`,
                    apiError.response ? apiError.response.data : apiError.message);
            }
        }
    } catch (dbError) {
        console.error('[Error] Database connection or query failed:', dbError);
    } finally {
        if (connection) {
            await connection.end();
        }
    }
}

// --- Scheduler ---
// Run this check every day at midnight (00:00 server time)
cron.schedule('0 0 * * *', () => {
    console.log('[System] Running daily due-date suspension check...');
    checkAndSuspendServers();
});

console.log('[System] Auto-suspension cron job started and listening.');

// ADD THIS LINE: Run it once immediately when the script starts
checkAndSuspendServers();
