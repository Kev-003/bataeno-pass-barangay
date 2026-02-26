import * as d3 from "d3";

export default function lineageD3() {
    return {
        treeData: null,
        siblings: [],
        svg: null,
        g: null,
        zoom: null,

        initD3() {
            try {
                const treeScript = this.$el.querySelector(
                    'script[type="application/json"].tree-data',
                );
                const siblingsScript = this.$el.querySelector(
                    'script[type="application/json"].siblings-data',
                );

                if (treeScript)
                    this.treeData = JSON.parse(treeScript.textContent);
                if (siblingsScript)
                    this.siblings = JSON.parse(siblingsScript.textContent);
            } catch (e) {
                console.error("[Lineage] JSON Parse Error:", e);
            }

            if (!this.treeData) return;
            // Delay slightly to ensure Alpine is ready and container has dimensions
            setTimeout(() => this.render(), 100);
        },

        debouncedRender() {
            if (this._timeout) clearTimeout(this._timeout);
            this._timeout = setTimeout(() => this.render(), 200);
        },

        render() {
            const container = document.getElementById("d3-tree-wrapper");
            if (!container) return;

            const width = container.clientWidth || 800;
            const height = container.clientHeight || 600;

            // Clear existing
            d3.select("#lineage-svg").selectAll("*").remove();

            const svg = d3
                .select("#lineage-svg")
                .attr("viewBox", [0, 0, width, height]);

            const g = svg.append("g");

            // Zoom behavior
            const zoom = d3
                .zoom()
                .scaleExtent([0.3, 3])
                .on("zoom", (event) => {
                    g.attr("transform", event.transform);
                });

            svg.call(zoom);

            // Hierarchy setup (Ancestor tree based on user)
            const root = d3.hierarchy(this.treeData);

            const nodeWidth = 220;
            const nodeHeight = 75;
            const levelPadding = 300;

            const treeLayout = d3
                .tree()
                .nodeSize([nodeHeight + 50, levelPadding]);

            treeLayout(root);

            // Horizontal layout logic
            root.each((d) => {
                const temp = d.x;
                d.x = d.y;
                d.y = temp;
            });

            // Center root (User) on the right side
            const rootXOffset = width - 150;
            const rootYOffset = height / 2;

            const dx = rootXOffset - root.x;
            const dy = rootYOffset - root.y;

            root.each((d) => {
                d.x = rootXOffset - d.depth * levelPadding;
                d.y = dy + d.y;
            });

            // Draw links
            g.append("g")
                .attr("fill", "none")
                .selectAll("path")
                .data(root.links())
                .join("path")
                .attr("class", (d) =>
                    d.target.data.is_deceased ? "link link-deceased" : "link",
                )
                .attr(
                    "d",
                    d3
                        .linkHorizontal()
                        .x((d) => d.x)
                        .y((d) => d.y),
                );

            // Draw nodes
            const node = g
                .append("g")
                .selectAll("g")
                .data(root.descendants())
                .join("g")
                .attr("transform", (d) => `translate(${d.x},${d.y})`)
                .attr("class", (d) => {
                    let cls = "node-group";
                    if (d.data.is_user) cls += " node-current";
                    if (d.data.is_deceased) cls += " node-deceased";
                    else
                        cls +=
                            d.data.gender === "Male" || d.data.gender === "male"
                                ? " node-male"
                                : " node-female";
                    return cls;
                });

            node.append("rect")
                .attr("class", "node-rect")
                .attr("x", -nodeWidth / 2)
                .attr("y", -nodeHeight / 2)
                .attr("width", nodeWidth)
                .attr("height", nodeHeight);

            node.append("text")
                .attr("class", "node-text-name")
                .attr("dy", "-0.2em")
                .attr("text-anchor", "middle")
                .text((d) =>
                    d.data.role
                        ? `${d.data.role}: ${d.data.name}`
                        : d.data.name,
                );

            node.append("text")
                .attr("class", "node-text-meta")
                .attr("dy", "1.2em")
                .attr("text-anchor", "middle")
                .text((d) =>
                    d.data.is_deceased
                        ? "Deceased"
                        : `${d.data.gender} · ${d.data.civil_status}`,
                );

            // Siblings
            if (this.siblings && this.siblings.length > 0) {
                const siblingG = g.append("g").attr("class", "siblings-group");
                this.siblings.forEach((s, i) => {
                    const sy = root.y + (nodeHeight + 30) * (i + 1);
                    const sx = root.x;

                    siblingG
                        .append("path")
                        .attr("class", "link")
                        .attr("stroke-dasharray", "4,4")
                        .attr(
                            "d",
                            `M ${root.x} ${root.y} C ${root.x} ${root.y + 20}, ${sx} ${sy - 20}, ${sx} ${sy}`,
                        );

                    const isMale = s.gender === "Male" || s.gender === "male";
                    const snode = siblingG
                        .append("g")
                        .attr("transform", `translate(${sx},${sy})`)
                        .attr(
                            "class",
                            s.is_deceased
                                ? "node-group node-deceased"
                                : isMale
                                  ? "node-group node-male"
                                  : "node-group node-female",
                        );

                    snode
                        .append("rect")
                        .attr("class", "node-rect")
                        .attr("x", -nodeWidth / 2)
                        .attr("y", -nodeHeight / 2)
                        .attr("width", nodeWidth)
                        .attr("height", nodeHeight)
                        .style("stroke-dasharray", "4,4");

                    snode
                        .append("text")
                        .attr("class", "node-text-name")
                        .attr("dy", "-0.2em")
                        .attr("text-anchor", "middle")
                        .text(s.name);

                    snode
                        .append("text")
                        .attr("class", "node-text-meta")
                        .attr("dy", "1.2em")
                        .attr("text-anchor", "middle")
                        .text(
                            s.is_deceased
                                ? "Deceased"
                                : `${s.gender} · Sibling`,
                        );
                });
            }

            // Focus on root
            const bounds = g.node().getBBox();
            const fullWidth = container.clientWidth || 800;
            const fullHeight = container.clientHeight || 600;

            const midX = bounds.x + bounds.width / 2;
            const midY = bounds.y + bounds.height / 2;

            const scale =
                0.8 /
                Math.max(bounds.width / fullWidth, bounds.height / fullHeight);

            svg.call(
                zoom.transform,
                d3.zoomIdentity
                    .translate(fullWidth / 2, fullHeight / 2)
                    .scale(Math.min(scale, 1.2))
                    .translate(-midX, -midY),
            );
        },
    };
}
