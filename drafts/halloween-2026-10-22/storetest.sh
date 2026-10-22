#!/bin/bash
# usage: storetest.sh "<items json array>" [coupon]
J=/tmp/claude-0/-home-user-novamira/00a8c5b6-c35e-5412-8f76-58c4e3bc367a/scratchpad/hw/jar.txt; rm -f $J
PV=$(cat /tmp/claude-0/-home-user-novamira/00a8c5b6-c35e-5412-8f76-58c4e3bc367a/scratchpad/hw/pv.txt)
C="curl -s -A Mozilla/5.0 -x $https_proxy -b $J -c $J"
$C -o /dev/null "https://peptrium.com/?sp_hw=vorschau&k=$PV"; [ -n "$AFF" ] && printf "peptrium.com\tFALSE\t/\tFALSE\t0\tslicewp_aff\t%s\n" "$AFF" >> $J
N=$($C -D - -o /dev/null https://peptrium.com/wp-json/wc/store/v1/cart | grep -i '^nonce:' | awk '{print $2}' | tr -d '\r')
for it in $(echo "$1" | python3 -c "import json,sys;[print(f\"{i[0]}:{i[1]}\") for i in json.load(sys.stdin)]"); do
 $C -o /dev/null -H "Nonce: $N" -H 'Content-Type: application/json' -d "{\"id\":${it%%:*},\"quantity\":${it##*:}}" https://peptrium.com/wp-json/wc/store/v1/cart/add-item; done
[ -n "$2" ] && $C -H "Nonce: $N" -H 'Content-Type: application/json' -d "{\"code\":\"$2\"}" https://peptrium.com/wp-json/wc/store/v1/cart/apply-coupon | python3 -c "import json,sys;d=json.load(sys.stdin);print('COUPON:',d.get('message') or [c['code'] for c in d.get('coupons',[])])"
$C https://peptrium.com/wp-json/wc/store/v1/cart | python3 -c "
import json,sys;d=json.load(sys.stdin)
for i in d['items']: print(' ',i['quantity'],'x',i['name'],'line',int(i['totals']['line_total'])/100,'sub',int(i['totals']['line_subtotal'])/100,[x['value'] for x in i['item_data']])
t=d['totals'];print('  items',int(t['total_items'])/100,'disc',int(t['total_discount'])/100,'fees',int(t['total_fees'])/100,'TOTAL',int(t['total_price'])/100, 'coupons',[c['code'] for c in d['coupons']])"
