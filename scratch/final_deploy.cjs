const { spawn } = require('child_process');

const password = '@Admineuro2026';
const command = 'cd domains/eurotaxisystem.site/public_html && git fetch origin main && git reset --hard origin/main && php artisan migrate --force && php artisan optimize && echo "---SUCCESS---"';

console.log('--- STARTING FORCED AUTOMATED DEPLOYMENT ---');

const ssh = spawn('ssh', [
    '-tt',
    '-p', '65002',
    '-o', 'StrictHostKeyChecking=no',
    '-o', 'PreferredAuthentications=password',
    'u747826271@195.35.62.133',
    command
]);

ssh.stdout.on('data', (data) => {
    const output = data.toString();
    console.log(`STDOUT: ${output}`);
});

ssh.stderr.on('data', (data) => {
    const output = data.toString();
    console.log(`STDERR: ${output}`);
    
    // Check for password prompt
    if (output.toLowerCase().includes('password:')) {
        console.log('>>> Password prompt detected. Sending credentials...');
        ssh.stdin.write(password + '\n');
    }
});

ssh.on('close', (code) => {
    console.log(`--- Process exited with code ${code} ---`);
    if (code === 0) {
        console.log('DEPLOYMENT FINISHED SUCCESSFULLY.');
    } else {
        console.log('DEPLOYMENT MIGHT HAVE FAILED. CHECK LOGS.');
    }
});
