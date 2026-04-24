const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026'
};

const conn = new Client();
conn.on('ready', () => {
    console.log('SSH Connection Ready...');
    conn.exec('cd /home/u747826271/domains/eurotaxisystem.site/public_html && php artisan tinker --execute="print_r(DB::select(\'SHOW COLUMNS FROM incident_involved_parties\'))"', (err, stream) => {
        if (err) throw err;
        stream.on('close', (code, signal) => {
            conn.end();
        }).on('data', (data) => {
            console.log(data.toString());
        }).stderr.on('data', (data) => {
            console.error('STDERR: ' + data);
        });
    });
}).connect(config);
