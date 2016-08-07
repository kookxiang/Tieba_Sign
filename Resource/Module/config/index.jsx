require("./style.scss");
import $ from "jquery";
import React from "react";
import Module from "../../Module";
import Checkbox from "react-toolbox/lib/checkbox";
import { RadioGroup, RadioButton } from "react-toolbox/lib/radio";
import { Button } from "react-toolbox/lib/button";
import { Snackbar } from "react-toolbox/lib/snackbar";
// import { Input } from "react-toolbox/lib/input";

class Config extends Module {
    constructor(id) {
        super(id);
        this.state = {
            accountExp: "加载中...",
            TelegramID: "",
            allowRenew: false,
            wenkuSign: false,
            zhidaoSign: false,
            mailSetting: -1,
            disabled: true,
            changed: false,
            savedComplete: false
        };
        this.addRoute(/^Dashboard\/Setting$/i);
    }
    componentDidMount(){
        this.loadSetting();
    }
    handleChange(field, value) {
        this.setState({ changed: true, [field]: value });
    }
    handleSnackbarTimeout() {
        this.setState({ savedComplete: false });
    }
    loadSetting(){
        $.ajax({
            url: "/Setting/Get.action"
        }).done(result => {
            if (result.code != 200) return;
            let newState = result.data;
            newState.disabled = false;
            this.setState(newState);
        });
    }
    saveSetting(){
        this.setState({ disabled: true });
        $.ajax({
            type: "POST",
            url: "/Setting/Save.action",
            data: {
                data: JSON.stringify(this.state)
            }
        }).done(result => {
            if (result.code != 200) {
                // Load last setting
                return this.loadSetting();
            }
            this.setState({ disabled: false, changed: false, savedComplete: true });
        }).fail(() => {
            this.loadSetting();
        });
    }
    render() {
        return (
            <div>
                <h3 className="setting-header">我的账号</h3>
                <p>{this.state.accountExp}</p>
                {/* <Input type='text' label='Telegram 用户 ID' value={this.state.TelegramID} onChange={this.handleChange.bind(this, "TelegramID")} type="number" hint="向机器人发送 /whoami 以查看" /> */}
                <h3 className="setting-header">邮件设置</h3>
                <RadioGroup disabled={this.state.disabled} value={this.state.mailSetting} onChange={this.handleChange.bind(this, "mailSetting")}>
                    <RadioButton label="不发送邮件通知" value={0} />
                    <RadioButton label="仅当失败时发送邮件通知" value={1} />
                    <RadioButton label="总是发送邮件通知" value={2} />
                </RadioGroup>
                <h3 className="setting-header">其他签到设置</h3>
                <Checkbox disabled={this.state.disabled} checked={this.state.zhidaoSign} label="百度知道签到" onChange={this.handleChange.bind(this, "zhidaoSign")} />
                <Checkbox disabled={this.state.disabled} checked={this.state.wenkuSign} label="百度文库签到" onChange={this.handleChange.bind(this, "wenkuSign")} />
                <p className="buttons">
                    <Button disabled={this.state.disabled || !this.state.changed} label="保存设置" onClick={this.saveSetting.bind(this)} raised primary />
                </p>
                <Snackbar action="关闭" active={this.state.savedComplete} label="您的设置已经保存成功." timeout={2000} type="cancel" onTimeout={this.handleSnackbarTimeout.bind(this)} />
            </div>
        );
    }
    goto() {
        this.updateTitle("个人设置");
    }
}

module.exports = Config;
