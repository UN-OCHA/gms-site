jQuery(function() {

    var radius = 750 / 2;
    var cluster = d3.layout.cluster()
        .size([360, radius - 120]);

    var diagonal = d3.svg.diagonal.radial()
        .projection(function(d) { return [d.y, d.x / 180 * Math.PI]; });

    d3.json(Drupal.settings.gms_ocha.path.circle, function(error, root) {

        var svg = d3.select("#circle_chart").append("svg")
            .attr('id','circle_svg')
            //        .attr("width", radius * 2)
            //        .attr("height", radius * 2)
            .attr("viewBox", "0 0 "+radius * 2+" "+radius * 2+"")
            .attr("perserveAspectRatio","xMinYMid")
            .append("g")
            .attr("transform", "translate(" + radius + "," + radius + ")");


        if (error) throw error;

        var nodes = cluster.nodes(root);

        var link = svg.selectAll("path.link")
            .data(cluster.links(nodes))
            .enter().append("path")
            .attr("class", "link")
            .attr("d", diagonal);

        var node = svg.selectAll("g.node")
            .data(nodes)
            .enter().append("g")
            .attr("class", "node")
            .attr("transform", function(d) { return "rotate(" + (d.x - 90) + ")translate(" + d.y + ")"; })

        node.append("circle")
            .attr("r", 4.5);

        node.append("text")
            .attr("dy", ".31em")
            .attr("text-anchor", function(d) { return d.x < 180 ? "start" : "end"; })
            .attr("transform", function(d) { return d.x < 180 ? "translate(8)" : "rotate(180)translate(-8)"; })
            .text(function(d) { return d.name; });
    });

    d3.select(self.frameElement).style("height", radius * 2 + "px");


});