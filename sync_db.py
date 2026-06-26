import paramiko

host = '130.94.34.24'
username = 'deployer'
password = 'tronifyteam'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=username, password=password)

    command = """
    cd /var/www/migrant_work_tw_be
    git fetch origin main
    git reset --hard origin/main
    
    # Run migration with fresh and seed
    php artisan migrate:fresh --seed --force
    """
    
    stdin, stdout, stderr = ssh.exec_command(command)
    
    print("Output:")
    print(stdout.read().decode())
    
    err = stderr.read().decode()
    if err:
        print("Error:")
        print(err)
        
    ssh.close()
except Exception as e:
    print(f"Connection failed: {str(e)}")
