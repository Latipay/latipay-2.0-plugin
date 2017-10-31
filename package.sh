rm -rf target
mkdir target
for plugin_folder in `ls`
do
   if [ -d $plugin_folder ]; then
      for version in  `ls $plugin_folder`
      do
         if [ -d $plugin_folder/$version ]; then
            cd $plugin_folder/$version
            zip -r ${plugin_folder}-${version}.zip *
            mv ${plugin_folder}-${version}.zip ../../target/
            cd -
         fi
      done
   fi
done
