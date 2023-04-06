FROM joomla:4.2.9-php8.1-apache

# Upgrade sed on 4.9 for bugfix with VirtioFS on MacOS (cf. https://forums.docker.com/t/sed-couldnt-open-temporary-file-xyz-permission-denied-when-using-virtiofs/125473/3)
RUN curl -o /tmp/libc6_2.36-8_$(dpkg --print-architecture).deb http://ftp.fr.debian.org/debian/pool/main/g/glibc/libc6_2.36-8_$(dpkg --print-architecture).deb; \
	dpkg -i /tmp/libc6_2.36-8_$(dpkg --print-architecture).deb; \
	rm /tmp/libc6_2.36-8_$(dpkg --print-architecture).deb

RUN curl -o /tmp/sed_4.9-1_$(dpkg --print-architecture).deb http://ftp.fr.debian.org/debian/pool/main/s/sed/sed_4.9-1_$(dpkg --print-architecture).deb; \
	dpkg -i /tmp/sed_4.9-1_$(dpkg --print-architecture).deb; \
	rm /tmp/sed_4.9-1_$(dpkg --print-architecture).deb

# Copy and set custom entrypoint script
COPY ["./scripts/entrypoint.sh","/entrypoint.sh"]
ENTRYPOINT [ "/entrypoint.sh"]

CMD ["apache2-foreground"]

