import paramiko
import sys

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect('130.94.34.24', username='deployer', password='tronifyteam', timeout=10)
    
    commands = [
        "sudo -n true && echo 'HAS_SUDO' || echo 'NO_SUDO'",
        "which supervisorctl || echo 'NO_SUPERVISOR'",
        "pm2 status || echo 'NO_PM2'"
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
