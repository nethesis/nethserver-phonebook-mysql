Name: nethserver-phonebook-mysql
Version: 2.0.12
Release: 1%{?dist}
Summary:	Public phonebook
License:	GPLv2
URL:            %{url_prefix}/%{name}
Source0:	%{name}-%{version}.tar.gz
BuildRequires:  nethserver-devtools
Requires:  nethserver-unixODBC
Requires:  nethserver-mysql
Requires:  php-odbc, php-mysql
Requires:  nodejs
Requires:  http-parser
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

%install
rm -rf %{buildroot}
(cd root ; find . -depth -print | cpio -dump %{buildroot})
rm -f %{name}-%{version}-%{release}-filelist
%{genfilelist} \
    --directory /etc/phonebook/sources.d 'attr(0777,root,root)' \
    --file /usr/share/phonebooks/phonebook 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/sogo_export.php 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/speeddial_and_rapidcode_export.php 'attr(0755,root,root)' \
    %{buildroot} > %{name}-%{version}-%{release}-filelist

%files -f %{name}-%{version}-%{release}-filelist
%defattr(-,root,root)
%dir %{_nseventsdir}/%{name}-update


%changelog
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


