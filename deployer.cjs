const { Client } = require('ssh2');

const config = {
    host: '195.35.62.133',
    port: 65002,
    username: 'u747826271',
    password: '@Admineuro2026',
    readyTimeout: 20000
};

const command = 'cd /home/u747826271/domains/eurotaxisystem.site/public_html && git fetch origin main && git reset --hard origin/main && rsync -av --exclude=\"storage\" public/ . && sed -i \'s|../vendor|vendor|g\' index.php && sed -i \'s|../bootstrap|bootstrap|g\' index.php && php artisan storage:link --force && php artisan migrate --force && php artisan optimize && echo \"---SUCCESS_DEPLOY---\"';

console.log('--- ROBUST DEPLOYMENT START ---');
console.log(`Connecting to ${config.host}:${config.port} as ${config.username}...`);

const conn = new Client();

conn.on('ready', () => {
    console.log('SSH Connection Ready.');
    console.log('Executing deployment commands...');
    
    conn.exec(command, (err, stream) => {
        if (err) {
            console.error('Execution Error:', err);
            conn.end();
            return;
        }
        
        stream.on('close', (code, signal) => {
            console.log(`\nStream Closed with code: ${code}`);
            conn.end();
            if (code === 0) {
                console.log('--- FINAL SUCCESS ---');
            } else {
                console.log('--- DEPLOYMENT FAILED ---');
            }
        }).on('data', (data) => {
            process.stdout.write(`STDOUT: ${data}`);
        }).stderr.on('data', (data) => {
            process.stderr.write(`STDERR: ${data}`);
        });
    });
}).on('error', (err) => {
    console.error('Connection Error:', err);
}).connect(config);
