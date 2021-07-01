<?php
/**
 * @content 文章标签界面
 * @author Z09418208_蒋伟伟
 * @create_time 2021-05-19
 */

require_once 'header.php'
?>

<style>
    .meta {
        padding: 6px;
    }
</style>

<link rel="stylesheet" href="static/css/main.css">


<div class="layui-body" style="left: 0;margin-top: 20px">

    <div class="layui-container">

        <div class="layui-row">
            <!--搜索菜单栏-->
            <div class="layui-form-item">
                <div class="layui-inline">

                    <label class="layui-form-label">标题</label>
                    <div class="layui-input-inline">
                        <input class="layui-input" id="reload-title"
                               autocomplete="off">
                    </div>

                    <label class="layui-form-label">标签</label>
                    <div class="layui-input-inline">
                        <input class="layui-input" id="reload-meta"
                               autocomplete="off">
                    </div>

                    <label class="layui-form-label">分类</label>
                    <div class="layui-input-inline">
                        <!--<input class="layui-input" id="reload-category"-->
                        <!--       autocomplete="off">-->

                        <!--从数据里查询分类-->
                        <select class="layui-select" id="reload-category" name="category" lay-verify="required">
                            <option value="">--请选择--</option>
                            <?php
                            require_once '../back_page/dao/category_dao.php';
                            $res = category_select('', 0, 100000);

                            foreach ($res as $item) {
                                $category = $item['category'];
                                echo "<option value='$category'> $category </option>";
                            }
                            ?>
                        </select>
                    </div>

                </div>

                <div class="layui-inline">
                    <!--注意此处button标签里的type属性-->
                    <button type="button" id="search-btn" class="layui-btn layui-btn-primary"
                            data-type="reload" lay-filter="data-search-btn">
                        <i class="layui-icon"></i> 搜 索
                    </button>
                </div>
            </div>


        </div>


        <!--文章内容-->
        <div class="layui-container" id="article-box">
            <hr>
            <h2>1. 您可以输入关键词进行搜索</h2>
            <h2>2. 敲击回车或者点击搜索按钮</h2>
            <h2>3. 例如输入Python,然后敲击回车</h2>
        </div>

        <div class="layui-container">
            <div class="layui-row">
                <div id="page" class="layui-col-md6 layui-col-md-offset3"></div>
            </div>
        </div>

    </div>
</div>


<script>
    layui.use(['laytpl', 'laypage'], function () {
        var laytpl = layui.laytpl,
            laypage = layui.laypage,
            $ = layui.jquery;


        $(document).keydown(function (event) {
            if (event.keyCode == 13) {
                searchBtnEvent();
            }
        })

        function searchBtnEvent() {
            var title = $('#reload-title').val(),
                category = $('#reload-category').val(),
                meta = $('#reload-meta').val();

            getArticleData(1, 10, '', title, category, meta);

            // 清空内容
            $('#reload-title').val('');
            $('#reload-category').val('');
            $('#reload-meta').val('');
        }

        $('#search-btn').bind('click', function () {
            searchBtnEvent();
        })




        // 页面数据加载
        // getArticleData();


        /**
         * 分页查询数据
         * @param page 页数
         * @param limit 每页信息条数
         * @param id 文章id
         * @param title 文章标题
         * @param category  分类
         * @param meta 标签
         */

        function getArticleData() {
            var page = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : 1;
            var limit = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;
            var id = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : '';
            var title = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : '';
            var category = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : '';
            var meta = arguments.length > 5 ? arguments[5] : '';

            $.ajax({
                url: '../back_page/controller/article/article_select_simple.php',
                type: 'get',
                data: {
                    page: page,
                    limit: limit,
                    id: id,
                    title: title,
                    category: category,
                    meta: meta
                },
                success: function success(res) {
                    console.log(res);
                    var data = res.data;
                    var count = res.count;

                    if (data.length == 0) {
                        $('#article-box')[0].innerHTML = '<br><h2>暂无相关文章，请切换分类查询...<h2>';
                    } else {
                        renderArticle(data);
                        renderPage(count, limit, page);
                    }
                },
                error: function error(res) {
                    layer.msg('数据加载失败,请检查网络连接。');
                }
            });
        }
        /**
         * 渲染数据到页面中
         * @param data 数组,数据内容
         */


        function renderArticle(data) {
            // 清空内容
            $('#article-box')[0].innerHTML = '';

            for (var i = 0; i < data.length; i++) {
                laytpl("\n                    <a href=\"./article_detail.php?article_id={{d.id}}\">\n                        <div class=\"card\">\n                            <div class=\"card-header\">{{d.title}}</div>\n                            <hr/>\n                            <div class=\"card-body\">\n                                <i class=\"layui-icon layui-icon-username layui-elip\t\">&nbsp;&nbsp;{{d.author}}</i>\n                                <i class=\"layui-icon layui-icon-time layui-elip\t\">&nbsp;&nbsp;{{d.create_time.slice(0,11)}}</i>\n                                <i class=\"layui-icon layui-icon-note layui-elip\t\">&nbsp;&nbsp;{{d.category}}</i>\n                            </div>\n                        </div>\n                    </a>\n                    ").render(data[i], function (string) {
                    $('#article-box')[0].innerHTML += string;
                });
            }
        }
        /**
         * 渲染分页功能
         * @param count 数据总数
         * @param limit 每页的数据数
         */


        function renderPage(count) {
            var limit = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 10;
            var curr = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 1;
            laypage.render({
                elem: 'page',
                count: count //数据总数，从服务端得到
                ,
                limit: limit,
                curr: curr,
                limits: [10, 20, 30],
                layout: ['prev', 'page', 'next', 'count', 'limit', 'skip'],
                jump: function jump(obj, first) {
                    //obj包含了当前分页的所有参数，比如：
                    console.log(obj.curr); //得到当前页，以便向服务端请求对应页的数据。

                    console.log(obj.limit); //得到每页显示的条数
                    // 保证不是第一次，防止无限递归

                    if (!first) {
                        getArticleData(obj.curr, obj.limit);
                    }
                }
            });
        }
    });
</script>


<?php
require_once 'footer.php';
?>
