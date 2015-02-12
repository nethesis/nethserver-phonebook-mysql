Name: nethserver-phonebook-mysql
Version:	1.2.9
Release:	1
Summary:	Copy mysql phonebook to LDAP every hour
License:	GPLv2
URL:            %{url_prefix}/%{name} 	
Source0:	%{name}-%{version}.tar.gz
BuildRequires:  nethserver-devtools
Requires:  nethserver-unixODBC
Requires:  nethserver-directory
Requires:  nethserver-mysql
Requires:  php-odbc
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
* Thu Feb 12 2015 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.10-1.ns6

* Wed Nov 12 2014 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.8-1.ns6
- Suppress warning and print error if can't connect to cti database

* Wed Nov 12 2014 Stefano Fancello <stefano.fancello@nethesis.it> - 1.2.7-1.ns6
- First NethVoice NG release


