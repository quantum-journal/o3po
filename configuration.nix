{ config, pkgs, ... }:

let

# For shits and giggles, let's package the responsive theme
responsiveTheme = pkgs.stdenv.mkDerivation {
  name = "responsive-theme";
  # Download the theme from the wordpress site
  src = pkgs.fetchurl {
    url = http://wordpress.org/themes/download/responsive.1.9.7.6.zip;
    sha256 = "1g1mjvjbx7a0w8g69xbahi09y2z8wfk1pzy1wrdrdnjlynyfgzq8";
  };
  # We need unzip to build this package
  buildInputs = [ pkgs.unzip ];
  # Installing simply means copying all files to the output directory
  installPhase = "mkdir -p $out; cp -R * $out/";
};

# Wordpress plugin 'akismet' installation example
akismetPlugin = pkgs.stdenv.mkDerivation {
  name = "akismet-plugin";
  # Download the theme from the wordpress site
  src = pkgs.fetchurl {
    url = https://downloads.wordpress.org/plugin/akismet.3.1.zip;
    sha256 = "1wjq2125syrhxhb0zbak8rv7sy7l8m60c13rfjyjbyjwiasalgzf";
  };
  # We need unzip to build this package
  buildInputs = [ pkgs.unzip ];
  # Installing simply means copying all files to the output directory
  installPhase = "mkdir -p $out; cp -R * $out/";
};

in

{
  services.mysql = {
    enable = true;
    package = pkgs.mysql;
  };

  services.httpd = {
    enable = true;
    logPerVirtualHost = true;
    adminAddr="drever@lrz.uni-muenchen.de";
    extraModules = [
      { name = "php5"; path = "${pkgs.php56}/modules/libphp5.so"; }
    ];

    virtualHosts = [
      {
        hostName = "O3PO-testintance";
        serverAliases = [ "O3PO-testinstance" ];

        extraSubservices =
        [
          {
            serviceType = "wordpress";
            dbPassword = "wordpress";
            wordpressUploads = "/data/uploads";
            themes = [ responsiveTheme ];
            plugins = [ akismetPlugin ];
          }
        ];
      }
    ];
  };
}
