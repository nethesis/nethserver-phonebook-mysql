Name: nethserver-phonebook-mysql
Version: 2.5.2
Release: 1%{?dist}
Summary:	Public phonebook
License:	GPLv2
URL:            %{url_prefix}/%{name}
Source0:	%{name}-%{version}.tar.gz
BuildRequires:  nethserver-devtools
BuildRequires:  npm
Requires:  nethserver-unixODBC
Requires:  nethserver-mysql
Requires:  php-odbc, php-mysql
Requires:  nodejs
Requires:  http-parser
Requires:  MySQL-python
Requires:  python-pycurl
Requires:  pyodbc
BuildArch:	noarch
AutoReq: no

BuildRequires: systemd
Requires(post): systemd
Requires(preun): systemd
Requires(postun): systemd

%description
Public phonebook

%post
%systemd_post phonebookjs.service

%preun
%systemd_preun phonebookjs.service

%prep
%setup -q

%build
%{makedocs}
perl createlinks
cd root/usr/share/phonebookjs
npm install

%install
rm -rf %{buildroot}
(cd root ; find . -depth -print | cpio -dump %{buildroot})
rm -f %{name}-%{version}-%{release}-filelist
%{genfilelist} %{buildroot} \
    --dir /etc/phonebook/sources.d 'attr(0777,root,root)' \
    --file /usr/share/phonebooks/phonebook 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/sogo_export.php 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/nethcti_export.php 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/speeddial_and_rapidcode_export.php 'attr(0755,root,root)' \
    --file /etc/sudoers.d/20_nethserver_phonebook_mysql '%config %attr(0440,root,root)' \
    > %{name}-%{version}-%{release}-filelist

%files -f %{name}-%{version}-%{release}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update


%changelog
* Tue May 30 2023 Stefano Fancello <stefano.fancello@nethesis.it> - 2.5.2-1
- Limit name and company length for NethPhone X3 client
- Add sample for VTE

* Wed Jan 26 2022 Stefano Fancello <stefano.fancello@nethesis.it> - 2.5.1-1
- Phonebookjs for nodejs 16 - nethesis/dev#6104

* Wed Dec 01 2021 Stefano Fancello <stefano.fancello@nethesis.it> - 2.5.0-1
- Add MSSQL support to centralized phonebook - nethesis/dev#6090

* Fri Nov 12 2021 Stefano Fancello <stefano.fancello@nethesis.it> - 2.4.2-1
- Custom phonebook sources missing from backup - Bug nethesis/dev#6084

* Tue Oct 05 2021 Stefano Fancello <stefano.fancello@nethesis.it> - 2.4.1-1
- Phonebook doesn't correctly shows extensions with accented characters - Bug nethesis/dev#6063

* Thu Sep 16 2021 Stefano Fancello <stefano.fancello@nethesis.it> - 2.4.0-1
- Add company column to tables indexes - nethesis/dev#6045
- nethserver-phonebook-mysql: MySQL `pbookuser` user's password is not keep updated - Bug nethesis/dev#6054
- Remove sogo user creation

* Mon Jun 28 2021 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.3.4-1
- Ldap phonebook doesn't respect the sizeLimit request parameter - Bug nethesis/dev#6034

* Tue Jan 26 2021 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.3.3-1
- phonebookjss fails after certificate-update - Bug nethesis/dev#5949

* Mon Dec 21 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.3.2-1
- Centralized phonebook fails to install - Bug nethesis/dev#5935

* Mon Dec 07 2020 Stefano Fancello <stefano.fancello@nethesis.it> - 2.3.1-1
- Certificate error message in Snom phone display - Bug nethesis/dev#5926

* Fri Nov 27 2020 Davide Principi <davide.principi@nethesis.it> - 2.3.0-1
- Phonebook CSV sources - nethesis/dev#5903

* Tue Oct 20 2020 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.2.4-1
- Phonebookjs(s) crashes when mysql connection is lost - Bug nethesis/dev#5857

* Wed Jul 01 2020 Stefano Fancello <stefano.fancello@nethesis.it> - 2.2.3-1
- Phonebook fails to start if name contains ";" character  - Bug nethesis/dev#5837

* Thu Apr 23 2020 Davide Principi <davide.principi@nethesis.it> - 2.2.2-1
- Display phonebook credentials in Nethgui - nethesis/dev#5781

* Fri Apr 10 2020 Stefano Fancello <stefano.fancello@nethesis.it> - 2.2.1-1
- Also restart ldaps phonebook after phonebook update

* Wed Apr 01 2020 Stefano Fancello <stefano.fancello@nethesis.it> - 2.2.0-1
- Add SSL and authentication to LDAP phonebook  - nethesis/dev#5755
- expand template and restart service on certificate-update

* Mon Oct 28 2019 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.1.3-1
- Phonebook: partial results for MSSQL queries - Nethesis/dev#5690

* Tue Jul 09 2019 Stefano Fancello <stefano.fancello@nethesis.it> - 2.1.2-1
- cron: fix phonebookjs restart time

* Fri May 24 2019 Stefano Fancello <stefano.fancello@nethesis.it> - 2.1.1-1
- Update speeddial_and_rapidcode_export.php: avoid spped dial duplication

* Thu May 09 2019 Stefano Fancello <stefano.fancello@nethesis.it> - 2.1.0-1
- Clear phonebook on save event. Nethesis/dev#5623
- Launch save event when a source is enabled/disabled/synced. Nethesis/dev#5623
- Execute nethserver-phonebook-mysql-conf after restore config. Nethesis/dev#5616

* Tue Mar 26 2019 Alessandro Polidori <alessandro.polidori@gmail.com> - 2.0.14-1
- NethVoice 14 Wizard: add the possibility to add personal phonebooks - nethesis/dev#5557

* Thu Mar 07 2019 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.13-1
- Convert search query string to lowercase nethesis/dev#5589

* Thu Jan 10 2019 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.12-1
- phonebookjs systemd unit: start after mysqld. nethesis/5554

* Fri Nov 23 2018 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.11-1
- Gigaset LDAP phonebook workaround  nethesis/5516
- nethcti_export.php: set mysql charset to utf8 nethesis/5514

* Tue Jun 26 2018 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.10-1
- Add rapidcode to centralized phonebook (#8) nethesis/dev#5422

* Fri Jun 01 2018 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.9-1
- Use PHP 5.6 on NethVoice14 and use PDO instead of deprecated mysql_ nethesis/dev#5406

* Fri Feb 16 2018 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.8-1
- Export from nethcti3 DB if available, nethcti2 instead nethesis/dev#5333

* Fri Feb 09 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.7-1
- Phonebook Js: do not return empty fields - Nethesis/dev#5328

* Mon Jan 29 2018 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.6-1
- Phonebookjs: wrong search results - Bug nethesis/dev#5300

* Fri Nov 24 2017 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.5-1
- nethvoice_extensions_export: use query on users table if userman_users table doesn't exists

* Tue Nov 14 2017 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.4-1
- Show only user's default extensions in phonebook. Nethesis/dev#5240

* Wed Jul 19 2017 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.3-1
- phonebookjs: implement results limit
- Fix phonebook scripts with new syntax

* Wed Jun 07 2017 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.2-2
- Require http-parser

* Fri Apr 28 2017 Edoardo Spadoni <edoardo.spadoni@nethesis.it> - 2.0.2-1
nethcti_export: add mysql escape, avoid output empty line. Nethesis/dev#5119

* Fri Apr 07 2017 Stefano Fancello <stefano.fancello@nethesis.it> - 2.0.1-1
- Add charset to mysql connection in samples
- Fix warning: Nethesis/dev#5102
- Implement save event. Nethesis/dev#5101
- Remove sogo support
- Remove the back_sql.la module definition

* Tue Dec 20 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.0-1
- Avoid creation of empty files named '1' - Nethesis/dev#5034
- Implement nodejs ldap replacement - Nethesis/dev#5036

* Tue Oct 18 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.1-1
- Remove nethserver-directory dependency
- Remove mysql.init code

* Thu Sep 22 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.4.0-1
- First NS 7 release

* Thu Mar 10 2016 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.5-1
- Add post_scripts logic [NH: 3991]

* Wed Jan 13 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.3.4-1
- Fix help on page with new LDAp tree. Refs #4024
- fix: migration-import called two times nethserver-phonebook-mysql-install

* Thu Nov 12 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.3.3-1
- substitute /etc/init/slapd.conf whith a custom one that starts slapd after mysqld. Refs #3950
- Overwrite ldap configuration backup to exclude phonebook connection from it. Refs #3951

* Fri Oct 16 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.3.2-1
- Sogo broken by last release because of the order of configuration lines. Refs #3906

* Thu Oct 01 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.3.1-1
- fix php tags in samples. Refs #3834

* Wed Sep 30 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.3.0-1
- On migration import, launch installation script to update phonebook permissions. Refs #3339
- Add NethVoice extensions into NethVoice phonebook. Refs #3450

* Wed Jun 24 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.14-1
- New phonebook implementation, add phonebook to sogo NH #3364

* Wed Apr 08 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.13-1.ns6
- speed_dial_export.php: change query to write empty fields in phonebook instead of NULL to workaround CTI bug #3510
- change sample scripts to workaround bug #3481
- nethserver-phonebook-mysql-install: removed phonebook regeneration script from update event. Refs #3560

* Wed Mar 25 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.12-1.ns6
 - use an appropriate template-begin for mysql files
 - use absolute path to check if nethvoice db key is present

* Thu Mar 05 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.11-1.ns6
  - added sample script to workaround bug #3481

* Mon Mar 02 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.10-1.ns6
  - update db password on migration
  - fix ui error when submitting changes

* Thu Feb 12 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.9-1.ns6
- add requires for php-odbc. Refs #3411

* Thu Feb 12 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.10-1.ns6

* Wed Nov 12 2014 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.8-1.ns6
- Suppress warning and print error if can't connect to cti database

* Wed Nov 12 2014 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.7-1.ns6
- First NethVoice NG release


