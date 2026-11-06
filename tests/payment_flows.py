"""Additional regression checks. Use fictional fixtures in te_crown_payment_test only.
Run after http_flows.py and enquiry_flows.py on a loopback test server.
"""
from pathlib import Path
import json,re,secrets,sys
"""Run against the isolated fixture database; never sends email.
Usage: python3 tests/http_flows.py http://127.0.0.1:8017
The fixture accounts use reserved example.invalid addresses.
"""
import http.cookiejar, json, re, sys, urllib.request, urllib.parse, urllib.error
BASE=sys.argv[1] if len(sys.argv)>1 else 'http://127.0.0.1:8017'
class Client:
 def __init__(self):
  self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
 def request(self,path,data=None):
  payload=None if data is None else urllib.parse.urlencode(data).encode()
  try:
   with self.opener.open(BASE+path,payload) as response: return response.status,response.read().decode(errors="replace"),response.url
  except urllib.error.HTTPError as error: return error.code,error.read().decode(errors="replace"),error.url
 def csrf(self,path):
  status,body,_=self.request(path);assert status==200,(path,status,body[:100]);return token(body),body
 def login(self,email):
  csrf,_=self.csrf('/pages/login.php');return self.request('/pages/login.php',{'csrf':csrf,'email':email,'password':'RepairTestPass123!'})
def token(body,name='csrf'):
 match=re.search(r'name="'+name+r'" value="([a-f0-9]+)"',body);assert match,(name,body[:100]);return match[1]
def check(condition,label):
 assert condition,label
 print('PASS:',label)

c=Client();check('/pages/account.php' in c.login('customer@example.invalid')[2],'customer fixture login')
a=Client();check('/admin/index.php' in a.login('admin@example.invalid')[2],'admin fixture login')
csrf,body=c.csrf('/pages/checkout.php')
data={'csrf':csrf,'request_key':token(body,'request_key'),'firstName':'Example','lastName':'Customer','phone':'0712345678','address':'1 Demo Street','city':'Johannesburg','province':'Gauteng','postalCode':'2000','notes':'Fictional test only','paymentMethod':'demo','cart':json.dumps([{'id':6,'quantity':1}])}
status,body,_=c.request('/pages/process-order.php',data);check(status==200,'new demo order for card validation');oid=json.loads(body)['order_id']
csrf,body=c.csrf('/pages/demo-payment.php?id='+str(oid));key=token(body,'request_key')
for field in ['demoCardName','demoCardNumber','demoCardExpiry','demoCardCvc']:
 el=re.search(r'<input[^>]*id="'+field+'"[^>]*>',body)[0]
 check(' name=' not in el,'private browser-only field '+field)
for attack in [{'outcome':'success'},{'outcome':'card','demo_card':'4242424242424242'},{'outcome':'card','demo_card':'real-card'},{'outcome':'card','demo_card':['approved']}]:
 status,_,_=c.request('/pages/demo-payment.php',{'csrf':csrf,'id':oid,'request_key':key,**attack});check(status==422,'reject forged or legacy payment payload')
check(c.request('/pages/demo-payment.php',{'csrf':'wrong','id':oid,'request_key':key,'outcome':'card','demo_card':'approved'})[0]==403,'card payment requires CSRF')
for choice in ['review','declined','approved']:
 csrf,b=c.csrf('/pages/demo-payment.php?id='+str(oid));status,b,_=c.request('/pages/demo-payment.php',{'csrf':csrf,'id':oid,'request_key':token(b,'request_key'),'outcome':'card','demo_card':choice})
 expected={'review':'Payment pending','declined':'Payment failed','approved':'Payment paid'}[choice]
 check(status==200 and expected in b,'saved customer result '+choice)
 ab=a.request('/admin/orders.php?id='+str(oid))[1]
 check({'review':'>Pending<','declined':'>Failed<','approved':'>Paid<'}[choice] in ab,'admin matches '+choice)
csrf,b=a.csrf('/admin/orders.php?id='+str(oid))
for status in ['shipped','completed']:
 code,b,_=a.request('/admin/orders.php',{'csrf':csrf,'id':oid,'status':status,'payment':'paid'});check(code==200,'admin fulfilment '+status)
 check(status in c.request('/pages/order-confirmation.php?id='+str(oid))[1],'customer sees fulfilment '+status)
# A second pending order is deliberately left for the visual preview.
csrf,b=c.csrf('/pages/checkout.php');data.update(csrf=csrf,request_key=token(b,'request_key'));code,b,_=c.request('/pages/process-order.php',data);preview=json.loads(b)['order_id']

print('Visual preview order:',preview)
# Crawl the current public/customer/admin routes and every referenced local image/script/stylesheet.
from html.parser import HTMLParser
from urllib.parse import urljoin,urlsplit
class Resources(HTMLParser):
 def __init__(self):super().__init__();self.resources=[]
 def handle_starttag(self,tag,attrs):
  d=dict(attrs)
  if tag in ['img','script'] and d.get('src'):self.resources.append(d['src'])
  if tag=='link' and d.get('rel')=='stylesheet' and d.get('href'):self.resources.append(d['href'])
paths=['/index.php','/pages/shop.php','/pages/product.php?id=1','/pages/about.php','/pages/contact.php','/pages/cart.php','/pages/checkout.php','/pages/account.php','/pages/enquiries.php','/pages/enquiry.php?id=1','/pages/order-confirmation.php?id='+str(oid),'/pages/demo-payment.php?id='+str(preview),'/pages/register.php','/pages/login.php','/pages/forgot-password.php','/pages/reset-password.php','/admin/index.php','/admin/products.php','/admin/categories.php','/admin/customers.php','/admin/orders.php','/admin/orders.php?id='+str(oid),'/admin/add-product.php','/admin/edit-product.php?id=1','/admin/enquiries.php','/admin/enquiries.php?id=1']
resources=set()
for path in paths:
 client=a if path.startswith('/admin') else c
 code,body,_=client.request(path)
 check(code==200 and not re.search(r'(Fatal error|Warning:|Uncaught |Parse error)',body),'page render '+path)
 parser=Resources();parser.feed(body)
 for r in parser.resources:
  url=urljoin(BASE+path,r)
  if urlsplit(url).netloc==urlsplit(BASE).netloc:resources.add(url.removeprefix(BASE))
for path in sorted(resources):check(c.request(path)[0]==200,'local asset '+path)
check(c.request('/pages/product.php?id=999999')[0]==404,'missing product returns HTTP 404')
print('Extra card, fulfilment, page and asset checks passed.')
