require "/usr/local/Library/Taps/homebrew/homebrew-php/Abstract/abstract-php-extension"

class Php71V8 < AbstractPhp71Extension
  init
  desc "PHP extension for V8 JavaScript engine"
  homepage "https://github.com/pinepain/php-v8"
  url "https://github.com/pinepain/php-v8/archive/v0.1.0.tar.gz"
  sha256 "b203da5b7fc72ef0cd71af26b409e2a1977c7e9a1c48648b33882c325ab755a3"
  head "https://github.com/pinepain/php-v8.git"

  bottle do
  end

  # NOTE: This formula depends on libv8, but actual "depends_on" dependency is not set yet

  # NOTE: this dependency is not valid as it takes core homebrew v8 formula, while own v8 already installed.
  #       It looks like vanilla v8 should be managed in a way like with PPA: libv8-x.y
  #depends_on "v8"

  def install
    ENV.universal_binary if build.universal?

    safe_phpize
    system "./configure", "--prefix=#{prefix}", phpconfig
    system "make"
    prefix.install "modules/v8.so"
    write_config_file if build.with? "config-file"
  end
end
