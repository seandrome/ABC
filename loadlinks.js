var link1 = '<dips id="asoc" ghre="';
var link10 = ' cahelek ';
var link2 = 'asoy">';
var link3 = 'border="0"></dips></dips></center>';
var gbr = 'dips id="img/';
var gbrtutup = '" ></dips></center>';
var gbrlink = 'border="0"></dips></center>';
var baru1 = '<a href="http://www.amazon.com/';
var baru10 = '/dp/';
var baru2 = '?tag=emobstore-20" target="_blank" rel="nofollow">';
var baru3 = 'border="0"></a></center>';
var gbrbaru = 'img src="http://gambar.pagodabox.com/';
var gbrbarututup = '/>';
function updateme()
{
var text = document.getElementsByTagName('body')[0].innerHTML;
text = replaceAll(text,gbr,gbrbaru);
text = replaceAll(text,gbrtutup,gbrbarututup);
text = replaceAll(text,link1,baru1);
text = replaceAll(text,link10,baru10);
text = replaceAll(text,link2,baru2);
text = replaceAll(text,link3,baru3);
document.getElementsByTagName('body')[0].innerHTML = text;
}
function replaceAll(txt, replace, with_this) {
return txt.replace(new RegExp(replace, 'g'),with_this);

}
setTimeout('updateme()',3000);