<?php
if(!defined('IN_KKFRAME')) exit();
?>
<h2>贴吧签到助手 配置向导</h2>
<div id="guide_pages">
<div id="guide_page_1">
<p>Hello，欢迎使用 贴吧签到助手~</p><br>
<p><b>这是一款免费软件，作者 <a href="http://www.ikk.me" target="_blank">kookxiang</a>，你可以从 www.kookxiang.com 上下载到这个项目的最新版本。</b></p>
<p>如果有人向您兜售本程序，麻烦您给个差评。</p><br>
<p>配置签到助手之后，我们会在每天的 0:30 左右为您自动签到。</p>
<p>签到过程不需要人工干预，您可以选择签到之后发送一封邮件报告到您的注册邮箱。</p><br>
<p>准备好了吗？点击下面的“下一步”按钮开始配置吧</p>
<p class="btns"><button class="btn submit" onclick="$('#guide_page_1').hide();checkCookieProxy();$('#guide_page_2').show();">下一步 &raquo;</button></p>
</div>
<div id="guide_page_2" class="hidden">
<p>首先，你需要绑定你的百度账号。</p><br>
<p>为了确保账号安全，我们只储存你的百度 Cookie，不会保存你的账号密码信息。</p>
<p>你可以通过修改密码的方式来让这些 Cookie 失效。</p><br>
<style>
.bind_mode .extension_info { padding: 10px 15px; margin: 0 5px 10px 20px; background: #f5f5f5; border: 1px solid #ddd; }
.bind_mode .extension_info p { line-height: 24px; margin-bottom: 0; }
</style>
<div class="bind_mode">
    <p>
        <label><input type="radio" name="bind_mode" value="auto" checked> 通过 Chrome 扩展一键获取 Cookie 绑定</label>
    </p>
    <iframe src="https://api.ikk.me/reborn/proxy.htm" id="cookie-proxy" class="hidden"></iframe>
    <div class="extension_info" id="cookie-proxy-info">
        <p>少年，看起来你的浏览器很辣鸡的样子，为何不考虑换下 Google Chrome 呢？</p>
    </div>
</div>
<div class="bind_mode">
    <p>
        <label><input type="radio" name="bind_mode" value="manual"> 手动填写 Cookie 绑定</label>
    </p>
    <form method="post" class="extension_info hidden" action="api.php?action=receive_cookie&amp;local=1&amp;formhash=<?php echo $formhash; ?>">
        <p>请填写完整的 Cookie 信息，格式如: BDUSS=xxxxxxxxxxxxx; BAIDUID=...</p>
        <p>
            <input id="cookie" name="cookie" type="text" placeholder="Cookie 信息" required />
            <input type="submit" value="确定" />
        </p>
    </form>
</div>
</div>
<div id="guide_page_manual" class="hidden"></div>
<div id="guide_page_3" class="hidden">
<p>一切准备就绪~</p><br>
<p>我们已经成功接收到你百度账号信息，自动签到已经准备就绪。</p>
<p>您可以点击 <a href="#setting">高级设置</a> 更改邮件设定，或更改其他附加设定。</p><br>
<p>感谢您的使用！</p><br>
<p>程序作者：kookxiang (<a href="http://www.ikk.me" target="_blank">http://www.ikk.me</a>)</p>
<p>赞助开发：<a href="http://go.ikk.me/donate" target="_blank">http://go.ikk.me/donate</a> (你的支持就是我的动力)</p>
</div>
</div>