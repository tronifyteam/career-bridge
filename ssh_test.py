import paramiko
import sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

print("Connecting to 130.94.34.24...")
try:
    ssh.connect('130.94.34.24', username='deployer', password='tronifyteam', timeout=10)
    print("Connected successfully!")
    
    commands = [
        "pwd",
        "cd /var/www/migrant_work_tw_be && pwd",
        "ls -la /var/www/migrant_work_tw_be | grep .env",
        "cat /var/www/migrant_work_tw_be/.env | grep -E 'MAIL_|PUSHER_|STRIPE_|DB_'"
    ]
    
    for cmd in commands:
        print(f"\n--- Running: {cmd} ---")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        out = stdout.read().decode()
        err = stderr.read().decode()
        
        if out:
            print(out)
        if err:
            print("ERROR:", err)
            
except Exception as e:
    print(f"Connection failed: {e}")
finally:
    ssh.close()
