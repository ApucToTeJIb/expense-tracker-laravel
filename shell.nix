{
  pkgs ? import <nixpkgs> { },
}:
pkgs.mkShell {
  buildInputs = [
    pkgs.php83
    pkgs.php83Packages.composer
    pkgs.mariadb
    pkgs.redis
    pkgs.nginx
  ];
  shellHook = ''
    echo "--- Laravel Dev Environment ---"
    mkdir  -p ./.nix/mysql ./.nix/redis ./.nix/nginx
    echo "Команды для запуска:"
    echo "1. redis-server --dir ./.nix/redis --port 6379 &"
    echo "2. mysql_install_db --datadir=./.nix/mysql"
    echo "3. mysqld --datadir=./.nix/datadir=./.nix/mysql --socket=./.nix/mysql/mysql.sock &"
  '';
}
