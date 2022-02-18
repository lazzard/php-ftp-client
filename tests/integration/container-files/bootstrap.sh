#!/bin/bash
set -eu
export TERM=xterm
# Bash Colors
green=`tput setaf 2`
bold=`tput bold`
reset=`tput sgr0`

# Functions
log() {
  if [[ "$@" ]]; then echo "${bold}${green}[VSFTPD `date +'%T'`]${reset} $@";
  else echo; fi
}

# If no env var for FTP_USER has been specified, use 'admin':
if [ "$FTP_USER" = "admin" ]; then
    export FTP_USER='admin'
fi

# If no env var has been specified, generate a random password for FTP_USER:
if [ "$FTP_PASS" = "random" ]; then
    export FTP_PASS=`cat /dev/urandom | tr -dc A-Z-a-z-0-9 | head -c${1:-16}`
fi

# Anonymous access settings
if [ "${ANONYMOUS_ACCESS}" = "true" ]; then
  sed -i "s|anonymous_enable=NO|anonymous_enable=YES|g" /etc/vsftpd/vsftpd.conf
  log "Enabled access for anonymous user."
fi

# Uploaded files world readable settings
if [ "${UPLOADED_FILES_WORLD_READABLE}" = "true" ]; then
  sed -i "s|local_umask=077|local_umask=022|g" /etc/vsftpd/vsftpd.conf
  log "Uploaded files will become world readable."
fi

# Custom passive address settings
if [ "${CUSTOM_PASSIVE_ADDRESS}" != "false" ]; then
  sed -i "s|pasv_address=|pasv_address=${CUSTOM_PASSIVE_ADDRESS}|g" /etc/vsftpd/vsftpd.conf
  log "Passive mode will advertise address ${CUSTOM_PASSIVE_ADDRESS}"
fi

# Create home dir and update vsftpd user db:
mkdir -p "/home/vsftpd/${FTP_USER}"
log "Created home directory for user: ${FTP_USER}"

echo -e "${FTP_USER}\n${FTP_PASS}" > /etc/vsftpd/virtual_users.txt
log "Updated /etc/vsftpd/virtual_users.txt"

/usr/bin/db_load -T -t hash -f /etc/vsftpd/virtual_users.txt /etc/vsftpd/virtual_users.db
log "Updated vsftpd database"

# Get log file path
export LOG_FILE=`grep vsftpd_log_file /etc/vsftpd/vsftpd.conf|cut -d= -f2`

# stdout server info:
if [ "${LOG_STDOUT}" = "true" ]; then
  log "Enabling Logging to STDOUT"
  mkdir -p /var/log/vsftpd
  touch ${LOG_FILE}
  tail -f ${LOG_FILE} | tee /dev/fd/1 &
elif [ "${LOG_STDOUT}" = "false" ]; then
  log "Logging to STDOUT Disabled"
else
  log "LOG_STDOUT available options are 'true/false'"
  exit 1
fi

cat << EOB
	SERVER SETTINGS
	---------------
	· FTP User: $FTP_USER
	· FTP Password: $FTP_PASS
	· Log file: $LOG_FILE
EOB

# Set permissions for FTP user
chown -R ftp:ftp /home/vsftpd/
log "Fixed permissions for newly created user: ${FTP_USER}"

log "VSFTPD daemon starting"
# Run vsftpd:
&>/dev/null /usr/sbin/vsftpd /etc/vsftpd/vsftpd.conf
