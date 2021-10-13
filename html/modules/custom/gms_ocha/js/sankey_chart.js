(function ($) {
    Drupal.behaviors.gms_ocha = {
        attach: function (context, drupalSettings) {
            $('#sankey_chart').load('gms_ocha', function() {
              console.log(drupalSettings.sankey_chart.path.sankey);
               var colors = {'color1': '#72a1c2', 'color2': '#8e8e8e', 'color3': '#d59977', 'color4': '#72a1c2', 'color5': '#8e8e8e', 'color6': '#d59977'};
                var formatNumber = d3.format(",.0f"),
                    format_million = function (d) {
                        var message = "Donor:" + d.name + "\nContributions: " + '$' + formatNumber(d.value / 1000000) + " M";
                        return message;
                    },
                    format = function (d) {
                        var message = "Donor:" + d.source.name + "\nRecipient:" + d.target.name + "\nContributions: " + '$' + formatNumber(d.value / 1000000) + " M";
                        return message;
                    },

                    color = d3.scale.category20();
                jQuery("#sankey_chart").empty();
                var units = "USD",
                    linkTooltipOffset = 62,
                    nodeTooltipOffset = 130;

                var margin = {top: 10, right: 70, bottom: 10, left: 110},
                    width = 720 - margin.left - margin.right,
                    height = 500 - margin.top - margin.bottom;
                var viewbox_width = width + margin.left + margin.right;
                var viewbox_height = height + margin.top + margin.bottom;
                /* Initialize tooltip */
                var tipLinks = d3.tip()
                    .attr('class', 'd3-tip')
                    .offset([-10,0]);

                var tipNodes = d3.tip()
                    .attr('class', 'd3-tip d3-tip-nodes')
                    .offset([-10, 0]);

                function formatAmount(val) {
                    return val.toLocaleString("en-US", {style: 'currency', currency: "USD"}).replace(/\.[0-9]+/, "");
                };


                // append the svg canvas to the page
                var svg = d3.select("#sankey_chart").append("svg")
                   // .attr("width", width + margin.left + margin.right)
                  //  .attr("height", height + margin.top + margin.bottom)
                    .attr("viewBox", "0 0 "+viewbox_width +" "+viewbox_height+"")
                    .attr("perserveAspectRatio","xMinYMid")
                    .attr("class", "sankey")
                    .call(tipLinks)
                    .call(tipNodes)
                    .append("g")
                    .attr("transform",
                        "translate(" + margin.left + "," + margin.top + ")");

                d3.json(drupalSettings.sankey_chart.path.sankey, function (energy) {
                    d3.select('#sankey_chart').selectAll('g').remove();

                    renderSankey();
                    tipLinks.html(function(d) {
                        var title, candidate;
                            candidate = d.source.name;
                            title = d.target.name;
                            var html =  '<div class="table-wrapper">'+
                                '<b>Donor: </b>'+candidate+'<br />'+
                                '<b>Recipient: </b>'+title+'<br />'+
                                '<b>Contributions: </b>'+formatAmount(d.value)+'<br /></div>';
                        return html;
                    });

                    tipNodes.html(function(d) {
                        var object = d3.entries(d),
                            nodeName = object[0].value,
                            linksTo = object[2].value,
                            linksFrom = object[3].value,
                            html;

                        html =  '<div class="table-wrapper">'+
                            '<h1>'+nodeName+'</h1>'+
                            '<table>';
                        for (i in linksFrom) {
                            html += '<tr>'+
                                '<td class="col-left">'+linksFrom[i].source.name+'</td>'+
                                '<td align="right">'+formatAmount(linksFrom[i].value)+'</td>'+
                                '</tr>';
                        }
                        for (i in linksTo) {
                            html += '<tr>'+
                                '<td class="col-left">'+linksTo[i].target.name+'</td>'+
                                '<td align="right">'+formatAmount(linksTo[i].value)+'</td>'+
                                '</tr>';
                        }
                        html += '</table></div>';
                        return html;
                    });

                    //d3.select(window).on('load.sankey', renderSankey).bind('load.sankey', renderSankey);
                    function renderSankey() {

                        myLinks = energy.links;
                        myNodes = energy.nodes;

                        svg = d3.select('.sankey')
                        // .attr("width", width)
                        //.attr("height", height)
                            .append("g");

                        sankey = d3.sankey()
                            .size([width, height])
                            .nodeWidth(5)
                            .nodePadding(10)
                            .nodes(myNodes)
                            .links(myLinks)
                            .layout(37);
                        path = sankey.link();

                        // add in the links
                        link = svg.append("g").selectAll(".link")
                            .data(myLinks)
                            .enter().append("path")
                            .attr("transform",
                                "translate(" + margin.left + "," + margin.top + ")")
                            .attr("class", "link")
                            .attr("d", path)
                            .style("stroke-width", function (d) {
                                return Math.max(1, d.dy);
                            })
                            .style("stroke", function (d) {
                                return d.color = color(d.target.id.replace(/ .*/, ""));
                            })
                            .sort(function(a, b) { return b.dy - a.dy; })
                            .on('mouseover', function(event) {
                                tipLinks
                                    .style("top", (d3.event.pageY - linkTooltipOffset) + "px")
                                    .style("left", function () {
                                        var left = (Math.max(d3.event.pageX - linkTooltipOffset, 10));
                                        left = Math.min(left, window.innerWidth - $('.d3-tip').width() - 400)
                                        return left + "px"; })
                            })
                            .on('mouseover', tipLinks.show)
                            .on('mouseout', tipLinks.hide);

                        // add in the nodes
                        node = svg.append("g").attr("transform",
                            "translate(" + margin.left + "," + margin.top + ")").selectAll(".node")
                            .data(myNodes)
                            .enter().append("g")
                            .attr("class", "node")
                            .attr("transform", function(d) {
                                return "translate(" + d.x + "," + d.y + ")"; });

                        // add the rectangles for the nodes
                        node.append("rect")
                            .attr("height", function(d) { return d.dy; })
                            .attr("width", sankey.nodeWidth()).on('mouseover', function(event) {
                                tipNodes
                                    .style("top", (d3.event.pageY - $('.d3-tip-nodes').height() - 20) + "px")
                                    .style("left", function () {
                                        var left = (Math.max(d3.event.pageX - nodeTooltipOffset, 10));
                                        left = Math.min(left, window.innerWidth - $('.d3-tip').width() - 20)
                                        return left + "px"; })
                        })
                            .style("fill", function(d) { if(d.x != 0){return d.color = '#666666';}else{return d.color = '#000000';}  })
                            .on('mouseover', tipNodes.show)
                            .on('mouseout', tipNodes.hide);

                            node.append("text")
                                .attr("x", sankey.nodeWidth()+2)
                                .attr("y", function(d) { return d.dy / 2; })
                                .attr("dy", ".35em")
                                .attr("text-anchor", "strat")
                                .style("font-family", 'Arial')
                                .style("font-size", '8px')
                                .style("color",'#545554')
                                .style("text-transform",'uppercase')
                                .attr("transform", null)
                                .text(function(d) { return d.name; })
                                .filter(function(d) { return d.x < width / 2; })
                                .attr("x", sankey.nodeWidth()-8)
                                .attr("text-anchor", "end");

                    }
                    // d3.select(window).on('resize.sankey', renderSankey).bind('load.sankey', renderSankey);
                });
            });

        }
    };
}(jQuery, Drupal, drupalSettings));
