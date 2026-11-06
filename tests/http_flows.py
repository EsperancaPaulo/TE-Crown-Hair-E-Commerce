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
   with self.opener.open(BASE+path,payload) as response: return response.status,response.read().decode(),response.url
  except urllib.error.HTTPError as error: return error.code,error.read().decode(),error.url
 def csrf(self,path):
  status,body,_=self.request(path);assert status==200,(path,status,body[:100]);return token(body),body
 def login(self,email):
  csrf,_=self.csrf('/pages/login.php');return self.request('/pages/login.php',{'csrf':csrf,'email':email,'password':'RepairTestPass123!'})
def token(body,name='csrf'):
 match=re.search(r'name="'+name+r'" value="([a-f0-9]+)"',body);assert match,(name,body[:100]);return match[1]
def check(condition,label):
 assert condition,label
 print('PASS:',label)
guest=Client();status,body,url=guest.request('/admin/index.php');check('/pages/login.php' in url,'guest admin access redirects to shared login')
for path in ['/.env','/database/schema.sql','/vendor/autoload.php','/includes/db.php','/test-email.php','/.git/config']:
 check(guest.request(path)[0]==404,'private path blocked '+path)
for path in ['/index.php','/pages/shop.php','/pages/product.php?id=1','/pages/about.php','/pages/cart.php','/pages/contact.php']:
 check(guest.request(path)[0]==200,'public page '+path)
status,body,url=guest.login('unverified@example.invalid');check('verify your email' in body and '/pages/login.php' in url,'unverified login is blocked')
customer=Client();status,body,url=customer.login('customer@example.invalid');check('/pages/account.php' in url and 'Repair Customer' in body,'customer shared login and dashboard')
check(customer.request('/admin/orders.php')[0]==403,'customer cannot access admin')
csrf,_=customer.csrf('/pages/checkout.php')
check(customer.request('/pages/process-order.php',{'csrf':'wrong'})[0]==403,'checkout rejects invalid CSRF')
def order(quantity=1,price=0,id=1):
 csrf,body=customer.csrf('/pages/checkout.php')
 data={'csrf':csrf,'request_key':token(body,'request_key'),'firstName':'Test','lastName':'Customer','phone':'0712345678','address':'1 Test Street','city':'Johannesburg','province':'Gauteng','postalCode':'2000','notes':'Fixture only','paymentMethod':'demo','cart':json.dumps([{'id':id,'quantity':quantity,'price':price,'name':'Forged client price'}])}
 return data,customer.request('/pages/process-order.php',data)
data,(status,body,_)=order(2)
check(status==200,'order persisted');oid=json.loads(body)['order_id']
status,replay,_=customer.request('/pages/process-order.php',data);check(json.loads(replay)['order_id']==oid,'checkout replay returns same order')
status,body,_=customer.request('/pages/order-confirmation.php?id='+str(oid));check('4,600.00' in body and 'Sandbox / demo' in body,'server price wins over forged browser price and demo is labeled')
other=Client();other.login('other@example.invalid');check(other.request('/pages/order-confirmation.php?id='+str(oid))[0]==404,'other customer cannot view order')
check(other.request('/pages/demo-payment.php?id='+str(oid))[0]==404,'other customer cannot pay order')
other_csrf,_=other.csrf('/pages/account.php')
status,body,_=other.request('/pages/demo-payment.php',{'csrf':other_csrf,'id':oid,'request_key':'c'*64,'outcome':'success','user_id':1})
check(status==404,'forged cross-customer payment POST rejected')
status,body,_=other.request('/pages/account.php?user_id=1');check('0 orders' in body and 'Repair Customer' not in body,'dashboard ignores client-supplied customer identity')
def pay(oid,outcome):
 csrf,body=customer.csrf('/pages/demo-payment.php?id='+str(oid));data={'csrf':csrf,'id':oid,'request_key':token(body,'request_key'),'outcome':'cancel' if outcome=='cancel' else 'card','demo_card':{'success':'approved','decline':'declined','pending':'review'}.get(outcome,'invalid')};return data,customer.request('/pages/demo-payment.php',data)
_,(status,body,_)=pay(oid,'bogus');check(status==422,'server rejects unrecognized simulation result')
_,(status,body,_)=pay(oid,'pending');check('Payment pending' in body and 'pending-review' in body,'pending-review card remains unpaid with retry')
_,(status,body,_)=pay(oid,'decline');check('Payment failed' in body and 'retry demo payment' in body,'decline persisted with retry')
payment,(status,body,_)=pay(oid,'success');check('Payment paid' in body and 'processing' in body and 'DEMO-' in body,'retry succeeds with saved demo transaction')
status,body,_=customer.request('/pages/demo-payment.php',payment);check(body.count('· success ·')==1,'repeated simulation creates no duplicate success')
_,(status,body,_)=order(999);check(status==422,'over-stock rejected')
_,(status,body,_)=order(-1);check(status==422,'negative quantity rejected')
_,(status,body,_)=order(1,id=99999);check(status==422,'nonexistent product rejected')
_,(status,body,_)=order(1);cancel_id=json.loads(body)['order_id']
cancel,(status,body,_)=pay(cancel_id,'cancel');check('cancelled' in body and 'Payment failed' in body,'cancellation persisted')
status,body,_=customer.request('/pages/demo-payment.php',cancel);check(body.count('· cancel ·')==1,'repeat cancellation creates no second transaction')
admin=Client();status,body,url=admin.login('admin@example.invalid');check('/admin/index.php' in url,'admin shared login redirects to dashboard')
for path in ['/admin/products.php','/admin/categories.php','/admin/add-product.php','/admin/edit-product.php?id=1','/admin/customers.php','/admin/orders.php']:
 status,page_body,_=admin.request(path);check(status==200 and 'Quick Links' not in page_body,'admin page without storefront footer '+path)
csrf,body=admin.csrf('/admin/categories.php')
category={'csrf':csrf,'action':'add','categoryName':"Repair's Test Category",'description':'Fixture category'}
status,body,_=admin.request('/admin/categories.php',category);check(status==200 and 'Category added.' in body,'admin creates category')
status,body,_=admin.request('/admin/categories.php',category);check('already exists' in body,'duplicate category handled without failure')
category_row=next(row for row in re.findall(r'<tr>.*?</tr>',body,re.S) if 'Test Category' in row)
category_id=int(re.search(r'categories.php\?edit=(\d+)',category_row)[1])
status,body,url=admin.request('/admin/categories.php',{'csrf':csrf,'action':'edit','categoryId':category_id,'categoryName':'Edited fixture category','description':'Updated fixture'})
check('success=updated' in url,'admin edits category')
status,body,url=admin.request('/admin/categories.php',{'csrf':csrf,'action':'delete','categoryId':category_id})
check('success=deleted' in url,'admin deletes empty category')
status,body,url=admin.request('/admin/categories.php',{'csrf':csrf,'action':'delete','categoryId':1})
check(status==422 and 'contains products' in body,'server protects category containing products')
csrf,_=admin.csrf('/admin/add-product.php')
product={'csrf':csrf,'productName':'Repair Fixture Product','category':1,'description':'Fixture only','price':'12.34','stock':'3','status':'active'}
status,body,url=admin.request('/admin/add-product.php',product);check('success=added' in url,'admin creates real product')
match=re.search(r'edit-product.php\?id=(\d+)',body);assert match;pid=int(match[1])
csrf,_=admin.csrf('/admin/edit-product.php?id='+str(pid));product.update(csrf=csrf,productId=pid,stock='4',price='13.45')
status,body,url=admin.request('/admin/edit-product.php?id='+str(pid),product);check('success=updated' in url,'admin edits product')
csrf,_=admin.csrf('/admin/products.php');status,body,url=admin.request('/admin/products.php',{'csrf':csrf,'action':'deactivate','productId':pid});check('success=deactivated' in url,'admin deactivates product')
check('Repair Fixture Product' not in guest.request('/pages/shop.php')[1],'deactivated product removed from customer catalogue')
csrf,body=admin.csrf('/admin/orders.php?id='+str(oid))
status,body,_=admin.request('/admin/orders.php',{'csrf':csrf,'id':oid,'status':'processing','payment':'pending'});check('simulator' in body,'admin cannot forge demo payment status')
csrf,_=admin.csrf('/admin/customers.php');admin.request('/admin/customers.php',{'csrf':csrf,'id':1})
check('/pages/login.php' in customer.request('/pages/account.php')[2],'deactivation revokes existing session')
admin.request('/admin/customers.php',{'csrf':csrf,'id':1})
customer.login('customer@example.invalid');csrf,_=customer.csrf('/pages/account.php');status,body,url=customer.request('/pages/logout.php',{'csrf':csrf});check('/pages/login.php' in url,'dedicated logout ends session')
check('/pages/login.php' in customer.request('/pages/account.php')[2],'logged out customer cannot return to protected dashboard')
status,body,_=guest.request('/pages/verify-email.php?token='+'b'*64);check('has expired' in body,'expired verification token rejected')
status,body,_=guest.request('/pages/verify-email.php?token='+'a'*64);check('successfully verified' in body,'valid verification token accepted')
status,body,_=guest.request('/pages/verify-email.php?token='+'a'*64);check('already been used' in body,'verification token cannot be reused')
status,body,url=guest.login('unverified@example.invalid');check('/pages/account.php' in url,'verified customer can sign in')
throttle=Client()
for _ in range(11): status,body,url=throttle.login('throttle@example.invalid')
check(status==429,'server-side login throttling')
print('All HTTP flow checks passed. No external email was sent.')
