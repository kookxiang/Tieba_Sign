require("./style.scss");
import $ from "jquery";
import React from "react";
import Dialog from "../../Misc/Dialog/Dialog";
import Module from "../../Module";
import { Button, IconButton } from "react-toolbox/lib/button";
import { Snackbar } from "react-toolbox/lib/snackbar";
import { List, ListItem } from "react-toolbox/lib/list";

class Invite extends Module {
    constructor(id) {
        super(id);
        this.state = {
            message: "",
            messageShow: false,
            availableInvite: 0,
            inviteCount: 0,
            allowShare: false,
            invites: [],
            disabled: true
        };
        this.addRoute(/^Dashboard\/Invite/i);
    }
    initialize() {
        this.loadInvites();
    }
    showSnakbar(message) {
        this.setState({ message: message, messageShow: true });
    }
    closeSnakbar() {
        this.setState({ messageShow: false });
    }
    loadInvites() {
        this.setState({ disabled: true });
        $.ajax({
            url: "/Invite/List.action"
        }).done(result => {
            if (result.code != 200)
                return;
            let newState = result.data;
            newState.disabled = false;
            this.setState(newState);
        });
    }
    onCopy(invite) {
        let inviteLink = invite.link;
        try {
            let inputElement = document.createElement("input");
            inputElement.value = inviteLink;
            inputElement.style.opacity = 0.01;
            document.body.appendChild(inputElement);
            inputElement.select();
            if (document.execCommand("copy")) {
                this.showSnakbar("邀请码已经复制到剪贴板");
            } else {
                prompt("复制失败，请手动按 Ctrl + C 复制", inviteLink);
            }
            document.body.removeChild(inputElement);
        } catch (e) {
            prompt("复制失败，请手动按 Ctrl + C 复制", inviteLink);
        }
    }
    onShare(invite) {
        this.setState({ disabled: true });
        $.ajax({
            url: "/Invite/" + invite.code + "/Share.action",
            type: "POST"
        }).done(result => {
            if (result.code != 200)
                return;
            this.setState({ disabled: false });
            this.showSnakbar("分享成功");
        }).fail(() => this.setState({ disabled: false }));
    }
    onDelete(invite) {
        Dialog.Confirm("你确定要删除这个邀请码吗？", "删除邀请码", () => {
            this.setState({ disabled: true });
            $.ajax({
                url: "/Invite/" + invite.code + "/Delete.action",
                type: "POST"
            }).done(result => {
                if (result.code != 200)
                    return;
                this.loadInvites();
                this.showSnakbar("邀请码已删除");
            });
        });
    }
    onGenerate() {
        this.setState({ disabled: true });
        $.ajax({
            url: "/Invite/Generate.action",
            type: "POST"
        }).done(result => {
            if (result.code != 200)
                return;
            this.loadInvites();
            this.showSnakbar("邀请码已生成");
        }).fail(() => this.setState({ disabled: false }));
    }
    render() {
        return (
            <div>
                <p>你还可以生成 {this.state.availableInvite < 0 ? "unlimited" : this.state.availableInvite} 个邀请链接。你可以通过链接邀请你的朋友来使用签到服务。</p>
                <p className={this.state.availableInvite == 0 ? "" : "hidden"}>你的邀请链接已经用完，要生成新的邀请链接，请与管理员取得联系。</p>
                <Button disabled={this.state.disabled || this.state.availableInvite == 0} onClick={this.onGenerate.bind(this)} label="生成新的邀请链接" primary raised/>
                <List>
                    {this.state.invites.map((invite, key) => {
                        if (invite.used) {
                            return <ListItem disabled key={key} caption={invite.code} legend={"已于 " + invite.usedTime + " 使用"}/>;
                        } else {
                            return <ListItem key={key} caption={invite.code} legend={"尚未使用，生成于 " + invite.generateTime} rightActions={[
                                this.state.allowShare ? <IconButton icon="share" key="share" disabled={this.state.disabled} onClick={this.onShare.bind(this, invite)}/> : null,
                                <IconButton key="delete" icon="delete" disabled={this.state.disabled}
                                onClick={this.onDelete.bind(this, invite)} />,
                                <IconButton key="copy" icon="content_copy" disabled={this.state.disabled} onClick={this.onCopy.bind(this, invite)} />
                            ]}/>;
                        }
                    })}
                </List>
                <Snackbar action="关闭" active={this.state.messageShow} label={this.state.message} timeout={3000} type="cancel" onClick={this.closeSnakbar.bind(this)} onTimeout={this.closeSnakbar.bind(this)}/>
            </div>
        );
    }
    goto() {
        this.updateTitle("邀请好友");
    }
}

module.exports = Invite;
