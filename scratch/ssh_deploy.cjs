const { spawn } = require('child_process');

const password = '@Admineuro2026';
const command = 'cd domains/eurotaxisystem.site/public_html && git reset --hard deploy && php artisan migrate --force && php artisan optimize && echo "---SUCCESS---"';

console.log('Starting SSH automation...');

const ssh = spawn('ssh', [
    '-p', '65002',
    '-o', 'StrictHostKeyChecking=no',
    '-o', 'PreferredAuthentications=password',
    'u747826271@195.35.62.133',
    command
]);

ssh.stdout.on('data', (data) => {
    console.log(`STDOUT: ${data}`);
});

ssh.stderr.on('data', (data) => {
    const output = data.toString();
    console.log(`STDERR: ${output}`);
    
    if (output.toLowerCase().includes('password:')) {
        console.log('Detected password prompt. Sending password...');
        ssh.stdin.write(password + '\n');
    }
});

ssh.on('close', (code) => {
    console.log(`Process exited with code ${code}`);
});
