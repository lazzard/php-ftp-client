FROM rockylinux:8

ENV FTP_USER=admin \
    FTP_PASS=random \
    LOG_STDOUT=false \
    ANONYMOUS_ACCESS=false \
    UPLOADED_FILES_WORLD_READABLE=false \
    CUSTOM_PASSIVE_ADDRESS=false

RUN \
  yum clean all && \
  yum install -y vsftpd ncurses && \
  yum clean all

COPY container-files /

EXPOSE 20-21 21100-21110

ENTRYPOINT ["/bootstrap.sh"]
