# Use allow-type or deny-type filter
uci set wireless.@wifi-iface[0].macfilter = "allow"
uci set wireless.@wifi-iface[0].macfilter = "deny"
 
# Append the MAC address to the list
uci add_list wireless.@wifi-iface[0].maclist = "11:22:33:44:55:66"
uci add_list wireless.@wifi-iface[0].maclist = "aa:bb:cc:dd:ee:ff"
 
# Check settings
uci show wireless.@wifi-iface[0]
 
# Save and apply
uci commit wireless
wifi reload