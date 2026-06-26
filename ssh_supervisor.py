import paramiko
import sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect('130.94.34.24', username='deployer', password='tronifyteam', timeout=10)
    
    conf = """[program:migrant-worker-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/migrant_work_tw_be/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deployer
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/migrant_work_tw_be/storage/logs/worker.log
stopwaitsecs=3600
"""
    
    # We will echo this into a temp file, then sudo cp it
    commands = [
        f"cat << 'EOF' > /tmp/migrant-worker-queue.conf\n{conf}\nEOF",
        "echo 'tronifyteam' | sudo -S cp /tmp/migrant-worker-queue.conf /etc/supervisor/conf.d/migrant-worker-queue.conf",
        "echo 'tronifyteam' | sudo -S supervisorctl reread",
        "echo 'tronifyteam' | sudo -S supervisorctl update",
        "echo 'tronifyteam' | sudo -S supervisorctl status migrant-worker-queue:"
    ]
    
    for cmd in commands:
        print(f"\n--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        out = stdout.read().decode().strip()
        err = stderr.read().decode().strip()
        
        if out: print("OUT:", out)
        if err: print("ERR:", err)
            
except Exception as e:
    print(f"Connection failed: {e}")
finally:
    ssh.close()
