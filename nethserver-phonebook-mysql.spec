Name: nethserver-phonebook-mysql
Version: 1.2.13
Release: 1%{?dist}
Summary:	Copy mysql phonebook to LDAP every hour
License:	GPLv2
URL:            %{url_prefix}/%{name} 	
Source0:	%{name}-%{version}.tar.gz
BuildRequires:  nethserver-devtools
Requires:  nethserver-unixODBC
Requires:  nethserver-directory
Requires:  nethserver-mysql
Requires:  php-odbc, openldap-servers-sql, php-mysql
BuildArch:	noarch

%description
Copy mysql phonebook to LDAP every hour

%prep
%setup -q

%build
%{makedocs}
perl createlinks

%install
(cd root   ; /usr/bin/find . -depth -print | /bin/cpio -dump $RPM_BUILD_ROOT)

/sbin/e-smith/genfilelist \
    --file /usr/share/phonebooks/phonebook 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/phonebook2ldap 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/sogo_export.php 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/nethcti_export.php 'attr(0755,root,root)' \
    --file /usr/share/phonebooks/speed_dial_export.php 'attr(0755,root,root)' \
    $RPM_BUILD_ROOT > %{name}-%{version}-%{release}-filelist

%files -f %{name}-%{version}-%{release}-filelist



%changelog
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


