ROOT_FOLDER=`pwd`
SED_IGNORE_FILES="magento/2.1.1/app/code/Magento5/Latipay/etc/config.xml|package.sh"
LATIPAY_API=$1
if [ "x$LATIPAY_API" = "x" ]; then
   echo "Package Plugin Connect Latipay 2.0 Production Environment"
   echo "Replace All api-staging.latipay.net To api.latipay.net"
   sed -i "s/api-staging.latipay.net/api.latipay.net/g" `grep "api-staging.latipay.net" -rl $ROOT_FOLDER | grep -Ev "$SED_IGNORE_FILES"`
else
   echo "Package Plugin Connect Latipay 2.0 Test Environment"
   echo "Replace All api.latipay.net To api-staging.latipay.net"
   sed -i "s/api.latipay.net/api-staging.latipay.net/g" `grep "api.latipay.net" -rl $ROOT_FOLDER | grep -Ev "$SED_IGNORE_FILES"`
fi

rm -rf target
mkdir target
for plugin_folder in `ls`
do
   if [ -d $plugin_folder ]; then
      for version in  `ls $plugin_folder`
      do
         if [ -d $plugin_folder/$version ]; then
            cd $plugin_folder/$version
            zip_name="${plugin_folder}-${version}.zip"
            if [ "x$LATIPAY_API" != "x" ]; then
               zip_name="${plugin_folder}-${version}-staging.zip"
            fi
            zip -r $zip_name * >/dev/null 2>&1
            mv $zip_name ../../target/
            cd $ROOT_FOLDER
            echo "target/$zip_name"
         fi
      done
   fi
done
