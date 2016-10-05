# 启动php内置服务器
# 根据具体项目修改路径
php -S 172.16.158.160:9090 -t /mnt/hgfs/vm_folder/myii2/all_projects/stock/backend/web

# 访问 172.16.158.160:9090
# 如果无法访问,可以是9090端口没有开启访问
# sudo iptables -I INPUT -p TCP --dport 9090 -j ACCEPT

