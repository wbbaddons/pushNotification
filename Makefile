FILES = $(shell find files -type f)
WCF_FILES = $(shell find files_wcf -type f)

all: be.bastelstu.max.wcf.pushNotification.tar be.bastelstu.max.wcf.pushNotification.tar.gz

be.bastelstu.max.wcf.pushNotification.tar.gz: be.bastelstu.max.wcf.pushNotification.tar
	gzip -9 < $< > $@

be.bastelstu.max.wcf.pushNotification.tar: files.tar files_wcf.tar *.xml LICENSE language/*.xml
	tar cvf be.bastelstu.max.wcf.pushNotification.tar --numeric-owner --exclude-vcs -- $^

files.tar: $(FILES)
files_wcf.tar: $(WCF_FILES)

%.tar:
	tar cvf $@ --numeric-owner --exclude-vcs -C $* -- $(^:$*/%=%)

clean:
	-rm -f files.tar

distclean: clean
	-rm -f be.bastelstu.max.wcf.pushNotification.tar
	-rm -f be.bastelstu.max.wcf.pushNotification.tar.gz

.PHONY: distclean clean
