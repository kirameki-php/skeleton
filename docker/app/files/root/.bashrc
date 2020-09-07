alias ls='ls --color=auto'
alias ll='ls -la'
alias l.='ls -d .* --color=auto'

if [ -r "/root/.git-prompt.sh" ]; then
    source /root/.git-prompt.sh
    PS1="[\u@\h \W]\$(__git_ps1 \"(\[\e[0;32m\]%s\[\e[0m\])\")\\$ "
fi

PATH=$PATH:/var/www/${APP_NAME}/vendor/bin
