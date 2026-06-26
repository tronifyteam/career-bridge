import paramiko
import time

host = "130.94.34.24"
user = "deployer"
password = "tronifyteam"
local_file_path = r"D:\INFORMATICS\FREELANCE\migrant_work_tw_be\migrant-work-tw-production-firebase-adminsdk-fbsvc-cbdcd61a38.json"
remote_path = "/var/www/migrant_work_tw_be"
new_credential_filename = "migrant-work-tw-production-firebase-adminsdk-fbsvc-cbdcd61a38.json"
remote_file_path = f"{remote_path}/{new_credential_filename}"

# Initialize SSH client
ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    print(f"Connecting to {host}...")
    ssh.connect(host, username=user, password=password)
    print("Connected successfully!")
    
    # 1. SFTP: Upload the credential file
    print(f"Uploading {new_credential_filename} to {remote_path}...")
    sftp = ssh.open_sftp()
    sftp.put(local_file_path, remote_file_path)
    sftp.close()
    print("Upload complete!")
    
    # 2. Run commands
    commands = [
        "pwd",
        f"cd {remote_path} && git pull origin main",
        f"cd {remote_path} && sed -i 's|^FIREBASE_CREDENTIALS=.*|FIREBASE_CREDENTIALS=\"{remote_file_path}\"|' .env",
        f"cd {remote_path} && php artisan config:clear",
        f"cd {remote_path} && php artisan cache:clear",
    ]
    
    for cmd in commands:
        print(f"\nExecuting: {cmd}")
        stdin, stdout, stderr = ssh.exec_command(cmd)
        
        # Wait for command to finish
        exit_status = stdout.channel.recv_exit_status()
        
        out = stdout.read().decode('utf-8')
        err = stderr.read().decode('utf-8')
        
        if out:
            print("Output:\n" + out.strip())
        if err:
            print("Error:\n" + err.strip())
            
        print(f"Exit status: {exit_status}")
        
except Exception as e:
    print(f"Error: {e}")
finally:
    ssh.close()
    print("\nSSH connection closed.")
