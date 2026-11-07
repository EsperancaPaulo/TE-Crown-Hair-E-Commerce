"""Registration UI regression. Run only against the isolated fixture shop; no email is sent."""
from pathlib import Path
import sys
exec(Path(__file__).with_name('http_flows.py').read_text().split('guest=Client()')[0])
c=Client();csrf,b=c.csrf('/pages/register.php')
status,b,_=c.request('/pages/register.php',{'csrf':csrf,'name':'Test','email':'bad','password':'Short1!','confirm_password':'Short1!'})
check(status==200 and 'valid email' in b and '<html' in b,'registration validation renders a full page')
status,b,_=c.request('/pages/register.php',{'csrf':csrf,'name':'Unverified','email':'expired@example.invalid','password':'RepairTestPass123!','confirm_password':'RepairTestPass123!'})
check(status==200 and 'Resend verification email' in b,'existing unverified registration offers resend')
status,b,_=c.request('/pages/register.php',{'csrf':csrf,'action':'resend','email':'unknown-regression-resend@example.invalid'})
check(status==200 and 'If this address' in b and '<html' in b,'unknown-account resend renders neutral result')
check(c.request('/pages/register.php',{'csrf':'wrong','action':'resend','email':'unknown-regression-resend@example.invalid'})[0]==403,'resend requires CSRF')
status,b,_=c.request('/pages/register.php',{'csrf':csrf,'name[]':'Test','email':'bad','password':'Short1!','confirm_password':'Short1!'})
check(status==200 and 'required fields' in b and '<html' in b,'malformed array field does not blank the page')
