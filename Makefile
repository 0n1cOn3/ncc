PHPCC=/usr/bin/php
PHPAB=/usr/local/bin/phpab
BUILD_PATH=build
SRC_PATH=src

autoload:
	# Generates/creates all the autoloader files
	make $(SRC_PATH)/ncc/ThirdParty/defuse/php-encryption/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/jelix/version/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/nikic/php-parser/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/php_parallel_lint/php_console_color/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-ctype/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-mbstring/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-uuid/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/Process/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/Uid/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/Filesystem/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/Symfony/Yaml/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/theseer/Autoload/autoload_spl.php
	make $(SRC_PATH)/ncc/ThirdParty/theseer/DirectoryScanner/autoload_spl.php
	make $(SRC_PATH)/ncc/autoload_spl.php
	cp $(SRC_PATH)/autoload/autoload.php $(SRC_PATH)/ncc/autoload.php

$(SRC_PATH)/ncc/ThirdParty/defuse/php-encryption/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/defuse/php-encryption/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/defuse/php-encryption

$(SRC_PATH)/ncc/ThirdParty/jelix/version/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/jelix/version/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/jelix/version

$(SRC_PATH)/ncc/ThirdParty/nikic/php-parser/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/nikic/PhpParser/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/nikic/PhpParser

$(SRC_PATH)/ncc/ThirdParty/php_parallel_lint/php_console_color/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/php_parallel_lint/php_console_color/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/php_parallel_lint/php_console_color

$(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-ctype/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-ctype/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-ctype

$(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-mbstring/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-mbstring/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-mbstring

$(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-uuid/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-uuid/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-uuid

$(SRC_PATH)/ncc/ThirdParty/Symfony/Process/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/Process/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/Process

$(SRC_PATH)/ncc/ThirdParty/Symfony/Uid/autoload_spl.php:
	$(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/Uid/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/Uid

$(SRC_PATH)/ncc/ThirdParty/Symfony/Filesystem/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/Filesystem/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/Filesystem

$(SRC_PATH)/ncc/ThirdParty/Symfony/Yaml/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/Symfony/Yaml/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/Symfony/Yaml

$(SRC_PATH)/ncc/ThirdParty/theseer/Autoload/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/theseer/Autoload/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/theseer/Autoload

$(SRC_PATH)/ncc/ThirdParty/theseer/DirectoryScanner/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/ThirdParty/theseer/DirectoryScanner/autoload_spl.php \
		$(SRC_PATH)/ncc/ThirdParty/theseer/DirectoryScanner

$(SRC_PATH)/ncc/autoload_spl.php:
	$(PHPCC) $(PHPAB) --output $(SRC_PATH)/ncc/autoload_spl.php \
		$(SRC_PATH)/ncc/Abstracts \
		$(SRC_PATH)/ncc/Classes \
		$(SRC_PATH)/ncc/CLI \
		$(SRC_PATH)/ncc/Exceptions \
		$(SRC_PATH)/ncc/Extensions \
		$(SRC_PATH)/ncc/Interfaces \
		$(SRC_PATH)/ncc/Managers \
		$(SRC_PATH)/ncc/Objects \
		$(SRC_PATH)/ncc/Runtime \
		$(SRC_PATH)/ncc/Utilities \
	  	$(SRC_PATH)/ncc/ncc.php \
	  	$(SRC_PATH)/ncc/Runtime.php

redist: autoload
	rm -rf $(BUILD_PATH)/src
	mkdir -p $(BUILD_PATH)/src
	cp -rf $(SRC_PATH)/ncc/* $(BUILD_PATH)/src
	cp $(SRC_PATH)/installer/installer $(BUILD_PATH)/src/INSTALL
	cp $(SRC_PATH)/installer/ncc.sh $(BUILD_PATH)/src/ncc.sh
	cp $(SRC_PATH)/config/ncc.yaml $(BUILD_PATH)/src/default_config.yaml;
	cp $(SRC_PATH)/config/ncc.yaml $(BUILD_PATH)/src/CLI/template_config.yaml;
	cp $(SRC_PATH)/installer/extension $(BUILD_PATH)/src/extension
	chmod +x $(BUILD_PATH)/src/INSTALL
	cp LICENSE $(BUILD_PATH)/src/LICENSE
	cp README.md $(BUILD_PATH)/src/README.md
	cp $(SRC_PATH)/installer/hash_check.php $(BUILD_PATH)/src/hash_check.php; $(PHPCC) $(BUILD_PATH)/src/hash_check.php; rm $(BUILD_PATH)/src/hash_check.php
	cp $(SRC_PATH)/installer/generate_build_files.php $(BUILD_PATH)/src/generate_build_files.php; $(PHPCC) $(BUILD_PATH)/src/generate_build_files.php; rm $(BUILD_PATH)/src/generate_build_files.php
	mkdir -p $(BUILD_PATH)/src/repositories
	cp -rf $(SRC_PATH)/default_repositories/*.json $(BUILD_PATH)/src/repositories

tar: redist
	cd $(BUILD_PATH)/src; tar -czvf ../ncc.tar.gz *

clean:
	rm -rf $(BUILD_PATH)
	rm -f $(SRC_PATH)/ncc/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/defuse/php-encryption/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/jelix/version/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/nikic/PhpParser/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/php_parallel_lint/php_console_color/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-ctype/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-mbstring/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/polyfill-uuid/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/Process/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/Uid/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/Filesystem/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/Symfony/Yaml/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/theseer/Autoload/autoload_spl.php
	rm -f $(SRC_PATH)/ncc/ThirdParty/theseer/DirectoryScanner/autoload_spl.php
