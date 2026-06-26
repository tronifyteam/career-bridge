import paramiko
import sys
import os

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

print("Connecting to VPS 130.94.34.24...")
try:
    ssh.connect('130.94.34.24', username='deployer', password='tronifyteam', timeout=10)
    print("Connected successfully!")
    
    # 1. Dump database
    print("Dumping PostgreSQL database...")
    stdin, stdout, stderr = ssh.exec_command("PGPASSWORD=tronifyteam pg_dump -U migrant_user -h 127.0.0.1 migrant_work_tw > /tmp/migrant_work_tw.sql")
    stdout.read()
    err = stderr.read().decode()
    if err:
        print("pg_dump warning/error:", err)
        
    # 2. Zip avatars and cvs
    print("Zipping avatars and cvs...")
    stdin, stdout, stderr = ssh.exec_command("cd /var/www/migrant_work_tw_be/storage/app/public && zip -r /tmp/storage_files.zip avatars cvs")
    stdout.read()
    
    # 3. Download files
    print("Downloading files via SFTP...")
    sftp = ssh.open_sftp()
    
    # Download SQL
    sftp.get('/tmp/migrant_work_tw.sql', 'migrant_work_tw.sql')
    print("Downloaded migrant_work_tw.sql")
    
    # Download Zip
    sftp.get('/tmp/storage_files.zip', 'storage_files.zip')
    print("Downloaded storage_files.zip")
    
    sftp.close()
    
    # 4. Clean up
    ssh.exec_command("rm /tmp/migrant_work_tw.sql /tmp/storage_files.zip")
    print("Cleanup done.")
    
except Exception as e:
    print(f"Migration failed: {e}")
finally:
    ssh.close()
