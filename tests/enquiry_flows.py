"""Private enquiry flow checks against the fictional repair database; no email sent."""
import http.cookiejar, json, re, sys, urllib.request, urllib.parse, urllib.error, uuid
BASE=sys.argv[1] if len(sys.argv)>1 else 'http://127.0.0.1:8017'
class Client:
 def __init__(self): self.opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
 def request(self,path,data=None):
  try:
   with self.opener.open(BASE+path,None if data is None else urllib.parse.urlencode(data).encode()) as response: return response.status,response.read().decode(),response.url
  except urllib.error.HTTPError as error: return error.code,error.read().decode(),error.url
 def page(self,path):
  status,body,url=self.request(path);assert status==200,(path,status,body[:100]);return body
 def login(self,email):
  body=self.page('/pages/login.php');status,body,url=self.request('/pages/login.php',{'csrf':field(body,'csrf'),'email':email,'password':'RepairTestPass123!'});assert status==200 and '/login.php' not in url

def field(body,name):
 match=re.search(r'name="'+name+r'" value="([^"]*)"',body);assert match,(name,body[:100]);return match[1]
def check(condition,label): assert condition,label;print('PASS:',label)
customer=Client();customer.login('customer@example.invalid')
other=Client();other.login('other@example.invalid')
admin=Client();admin.login('admin@example.invalid')
guest=Client();nonce=uuid.uuid4().hex[:10];subject='Private enquiry '+nonce
form=customer.page('/pages/contact.php')
data={'csrf':field(form,'csrf'),'request_key':field(form,'request_key'),'name':'Forged name','email':'other@example.invalid','user_id':3,'subject':subject,'message':'Question with <script>alert(1)</script> and a second line.\nPlease help.'}
status,body,url=customer.request('/pages/contact.php',{**data,'csrf':'invalid'});check(status==403,'contact requires valid CSRF')
status,body,url=customer.request('/pages/contact.php',{**data,'message':'x'*5001});check(status==422,'overlong enquiry rejected')
status,body,url=customer.request('/pages/contact.php',data);check(status==200 and '/pages/enquiry.php?id=' in url,'signed-in Contact submission persists and opens private enquiry')
eid=int(re.search(r'id=(\d+)',url)[1])
check('&lt;script&gt;' in body and '<script>alert(1)</script>' not in body,'original message is safely escaped')
status,body,url=customer.request('/pages/contact.php',data);check(int(re.search(r'id=(\d+)',url)[1])==eid,'contact resubmission returns original enquiry')
check(subject in customer.page('/pages/account.php'),'customer dashboard shows own enquiry')
check(subject not in other.page('/pages/account.php?user_id=1'),'other dashboard does not expose enquiry through supplied user ID')
check(subject not in other.page('/pages/enquiries.php?user_id=1'),'customer enquiry list is scoped to session owner')
check(other.request('/pages/enquiry.php?id='+str(eid)+'&user_id=1')[0]==404,'other customer cannot open enquiry')
csrf=field(other.page('/pages/account.php'),'csrf')
check(other.request('/pages/enquiry.php?id='+str(eid),{'csrf':csrf,'status':'resolved','user_id':1})[0]==405,'customer detail endpoint cannot be used to modify enquiry')
check(customer.request('/admin/enquiries.php')[0]==403,'customer cannot open admin enquiry tools')
check(customer.request('/admin/enquiries.php',{'csrf':data['csrf'],'id':eid,'reply':'Forged response','status':'resolved'})[0]==403,'customer cannot submit admin replies')
adminbody=admin.page('/admin/enquiries.php?id='+str(eid))
check('Repair Customer · customer@example.invalid' in adminbody and 'Forged name' not in adminbody,'authenticated session determines enquiry customer identity')
check('Quick Links' not in adminbody,'admin enquiry view has no storefront footer')
def response_form(eid,message,status):
 body=admin.page('/admin/enquiries.php?id='+str(eid));return {'csrf':field(body,'csrf'),'id':eid,'revision':field(body,'revision'),'request_key':field(body,'request_key'),'reply':message,'status':status}
reply='Reply '+nonce+' <strong>fixture</strong>\nWe can help with your question.'
post=response_form(eid,reply,'replied')
status,body,url=admin.request('/admin/enquiries.php',post);check(status==200 and 'Enquiry updated' in body,'admin response and Replied status saved')
status,body,url=admin.request('/admin/enquiries.php',post);check(body.count('Reply '+nonce)==1,'duplicate response POST creates no duplicate reply')
body=customer.page('/pages/account.php');check('Reply '+nonce in body and 'Replied' in body,'customer dashboard displays admin response and current status')
check('&lt;strong&gt;fixture&lt;/strong&gt;' in body,'admin reply is safely escaped in dashboard')
status,body,_=admin.request('/admin/enquiries.php',{**post,'request_key':'f'*64,'status':'resolved'});check(status==422 and 'has changed' in body,'stale admin updates rejected')
post=response_form(eid,'Resolved response '+nonce,'resolved');status,body,_=admin.request('/admin/enquiries.php',post);check(status==200,'admin saves follow-up response and Resolved status')
body=customer.page('/pages/enquiry.php?id='+str(eid));check('Resolved' in body and 'Reply '+nonce in body and 'Resolved response '+nonce in body,'customer sees complete persisted response conversation')
check('Resolved response '+nonce not in other.page('/pages/account.php'),'admin responses remain private from other customer')
post=response_form(eid,'','unsupported');check(admin.request('/admin/enquiries.php',post)[0]==422,'unknown enquiry status rejected')
post=response_form(eid,'','open');post['csrf']='invalid';check(admin.request('/admin/enquiries.php',post)[0]==403,'admin changes require CSRF')
# A guest claims the same email as another registered account; it must stay unlinked.
gform=guest.page('/pages/contact.php');gdata={'csrf':field(gform,'csrf'),'request_key':field(gform,'request_key'),'name':'Guest fixture','email':'other@example.invalid','subject':'Guest enquiry '+nonce,'message':'Guest message fixture'}
status,body,url=guest.request('/pages/contact.php',gdata);check(status==200 and 'Guest enquiries do not appear in an account' in body,'guest submission is saved with honest tracking notice')
gid=int(re.search(r'guest enquiry #EQ(\d+)',body)[1])
check('Guest enquiry '+nonce not in other.page('/pages/account.php'),'guest email match does not attach enquiry to account')
check(other.request('/pages/enquiry.php?id='+str(gid))[0]==404,'matching-email customer cannot read guest enquiry')
check('/pages/login.php' in guest.request('/pages/enquiry.php?id='+str(gid))[2],'guest cannot access private enquiry detail')
body=admin.page('/admin/enquiries.php?id='+str(gid));check('Guest enquiry. This record is not linked' in body,'admin can identify unlinked guest enquiry')
post=response_form(gid,'Internal guest fixture reply','replied');status,body,_=admin.request('/admin/enquiries.php',post);check(status==200 and 'internal only' in body,'guest response stays internal with no false email delivery claim')
check(other.request('/pages/enquiry.php?id='+str(gid))[0]==404,'guest reply does not grant matching-email customer access')
check('Guest enquiry '+nonce in admin.page('/admin/enquiries.php?status=replied'),'admin can filter enquiry status')
print('All enquiry checks passed. No external email was sent.')
