/**
 * 確認画面
 *
 **/
function  Start(e) {
  'use strict';

  if (confirm("サーバーを起動します。\n" +
              "サーバーの停止は手動で行ってください。"))  {
    document.getElementById('start_' + e.dataset.id).submit();
  }
}

function  Stop_Run(e) {
  'use strict';

  if (confirm('サーバーを停止します。'))  {
    document.getElementById('stop_' + e.dataset.id).submit();
  }
}

function  toManual(e) {
  'use strict';

  if (confirm("本日の運用を手動モードに切り替えます。\n"
          +  '停止予定時刻は無効になります。'))  {
    document.getElementById('manual_' + e.dataset.id).submit();
  }
}

