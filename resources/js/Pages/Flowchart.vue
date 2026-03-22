<script setup>
import { onMounted, onBeforeUnmount, nextTick } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { VueFlow, useVueFlow, Handle, Position, ConnectionMode, MarkerType, getRectOfNodes, getTransformForBounds } from '@vue-flow/core';
import { Background } from '@vue-flow/background';
import { Controls } from '@vue-flow/controls';
import { toPng, toJpeg } from 'html-to-image';
import { jsPDF } from 'jspdf';

// Import Vue Flow styles
import '@vue-flow/core/dist/style.css';
import '@vue-flow/core/dist/theme-default.css';
import '@vue-flow/controls/dist/style.css';

const props = defineProps({
    project: {
        type: Object,
        default: null,
    },
});

const { 
    addNodes, 
    addEdges, 
    onConnect, 
    toObject, 
    fromObject, 
    fitView,
    nodes,
    edges,
    onNodesChange,
    onEdgesChange,
    findNode,
    updateEdge,
    onEdgeUpdate,
    getSelectedNodes,
    getSelectedEdges,
    removeNodes,
    removeEdges,
    endConnection,
} = useVueFlow();

const form = useForm({
    id: props.project?.id || null,
    title: props.project?.title || 'Untitled Blueprint',
    description: props.project?.description || '',
    content: props.project?.content || JSON.stringify({ nodes: [], edges: [] }),
});

// Initialize flowchart data
onMounted(() => {
    if (props.project?.content) {
        try {
            const data = JSON.parse(props.project.content);
            if (data.nodes) {
                data.nodes = data.nodes.map(node => ({
                    ...node,
                    type: node.type || 'flowchart'
                }));
            }
            if (data.edges) {
                data.edges = data.edges.map(edge => ({
                    ...edge,
                    type: edge.type || 'smoothstep',
                    style: { stroke: '#000', strokeWidth: 3, fill: 'none' },
                    updatable: true,
                    markerEnd: { type: MarkerType.ArrowClosed, color: '#000' }
                }));
            }
            fromObject(data);
            nextTick(() => {
                setTimeout(() => fitView(), 100);
            });
        } catch (e) {
            console.error('Failed to load project data:', e);
        }
    }
});

const handleDeleteKey = (event) => {
    if (event.key !== 'Delete' && event.key !== 'Backspace') {
        return;
    }

    const target = event.target;
    if (target instanceof HTMLElement) {
        const isTypingTarget = target.tagName === 'INPUT' || target.tagName === 'TEXTAREA' || target.isContentEditable;
        if (isTypingTarget) {
            return;
        }
    }

    const selectedNodes = getSelectedNodes.value;
    const selectedEdges = getSelectedEdges.value;

    if (!selectedNodes.length && !selectedEdges.length) {
        return;
    }

    event.preventDefault();
    event.stopPropagation();

    if (selectedEdges.length) {
        removeEdges(selectedEdges);
    }

    if (selectedNodes.length) {
        removeNodes(selectedNodes, true);
    }
};

onMounted(() => {
    window.addEventListener('keydown', handleDeleteKey, true);
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleDeleteKey, true);
});

// Handle manual connections (Drag & Drop)
onConnect((params) => {
    addEdges({
        ...params,
        type: 'smoothstep',
        animated: false,
        style: { stroke: '#000', strokeWidth: 3, fill: 'none' },
        markerEnd: { type: MarkerType.ArrowClosed, color: '#000' },
    });

    endConnection();
});

// Handle edge reconnection
onEdgeUpdate(({ edge, connection }) => {
    updateEdge(edge, connection);
});

// Quick Add Node Function
const quickAdd = (sourceId, direction) => {
    const sourceNode = findNode(sourceId);
    if (!sourceNode) return;

    const id = `node_${Date.now()}`;
    const offset = 250; 
    
    let newPos = { x: sourceNode.position.x, y: sourceNode.position.y };
    let sourceHandle = 'bottom';
    let targetHandle = 'top';

    switch (direction) {
        case 'top': newPos.y -= offset; sourceHandle = 'top'; targetHandle = 'bottom'; break;
        case 'bottom': newPos.y += offset; sourceHandle = 'bottom'; targetHandle = 'top'; break;
        case 'left': newPos.x -= offset; sourceHandle = 'left'; targetHandle = 'right'; break;
        case 'right': newPos.x += offset; sourceHandle = 'right'; targetHandle = 'left'; break;
    }

    addNodes({
        id,
        type: 'flowchart',
        label: 'NEW ELEMENT',
        position: newPos,
        data: { label: 'NEW ELEMENT' },
    });

    setTimeout(() => {
        addEdges({
            id: `edge_${Date.now()}`,
            source: sourceId,
            target: id,
            sourceHandle: sourceHandle,
            targetHandle: targetHandle,
            type: 'smoothstep',
            style: { stroke: '#000', strokeWidth: 3, fill: 'none' },
            markerEnd: { type: MarkerType.ArrowClosed, color: '#000' },
            updatable: true,
        });
    }, 100);
};

// Sidebar Functions
const addBox = () => {
    const selectedNodes = getSelectedNodes.value;
    const selectedNode = selectedNodes.length > 0 ? selectedNodes[0] : null;
    const id = `node_${Date.now()}`;
    const position = selectedNode 
        ? { x: selectedNode.position.x + 250, y: selectedNode.position.y }
        : { x: Math.random() * 200 + 100, y: Math.random() * 200 + 100 };

    addNodes({
        id,
        type: 'flowchart',
        label: 'NEW BOX',
        position,
    });

    if (selectedNode) {
        setTimeout(() => {
            addEdges({
                id: `edge_${Date.now()}`,
                source: selectedNode.id,
                target: id,
                sourceHandle: 'right',
                targetHandle: 'left',
                type: 'smoothstep',
                style: { stroke: '#000', strokeWidth: 3, fill: 'none' },
                markerEnd: { type: MarkerType.ArrowClosed, color: '#000' },
                updatable: true,
            });
        }, 100);
    }
};

const addText = () => {
    const id = `node_${Date.now()}`;
    addNodes({
        id,
        type: 'flowchart-text',
        label: 'TEXT LABEL',
        position: { x: Math.random() * 200 + 100, y: Math.random() * 200 + 100 },
    });
};

// Save the project
const saveProject = () => {
    form.content = JSON.stringify(toObject());
    form.post(route('projects.save'));
};

// Remove node
const removeNode = (id) => {
    const node = findNode(id);
    if (!node) {
        return;
    }

    removeNodes([node], true);
};

// Update node label
const updateNodeLabel = (id, newLabel) => {
    const node = nodes.value.find(n => n.id === id);
    if (node) {
        node.label = newLabel;
        node.data = { ...(node.data || {}), label: newLabel };
    }
};

// Export Functions
const exportFlowchart = async (format) => {
    const el = document.querySelector('.vue-flow__viewport');
    if (!el) return;

    // Helper to download file
    const download = (dataUrl, extension) => {
        const a = document.createElement('a');
        a.href = dataUrl;
        a.download = `${form.title.replace(/\s+/g, '_').toLowerCase()}_${Date.now()}.${extension}`;
        a.click();
    };

    const currentNodes = nodes.value || [];
    if (!currentNodes.length) {
        alert('No nodes to export.');
        return;
    }

    const bounds = getRectOfNodes(currentNodes);
    const padding = 80;
    const exportWidth = Math.max(1, Math.ceil(bounds.width + padding * 2));
    const exportHeight = Math.max(1, Math.ceil(bounds.height + padding * 2));
    const viewport = getTransformForBounds(bounds, exportWidth, exportHeight, 0.01, 4, padding / Math.max(exportWidth, exportHeight));

    try {
        const options = {
            backgroundColor: '#f8f9fa',
            quality: 1,
            pixelRatio: 2,
            width: exportWidth,
            height: exportHeight,
            style: {
                width: `${exportWidth}px`,
                height: `${exportHeight}px`,
                transform: `translate(${viewport.x}px, ${viewport.y}px) scale(${viewport.zoom})`,
                transformOrigin: '0 0',
            },
        };

        if (format === 'png') {
            const dataUrl = await toPng(el, options);
            download(dataUrl, 'png');
        } else if (format === 'jpg') {
            const dataUrl = await toJpeg(el, options);
            download(dataUrl, 'jpg');
        } else if (format === 'pdf') {
            const dataUrl = await toPng(el, options);
            const pdf = new jsPDF({
                orientation: exportWidth >= exportHeight ? 'landscape' : 'portrait',
                unit: 'px',
                format: [exportWidth, exportHeight],
            });
            pdf.addImage(dataUrl, 'PNG', 0, 0, exportWidth, exportHeight);
            pdf.save(`${form.title.replace(/\s+/g, '_').toLowerCase()}_${Date.now()}.pdf`);
        }
    } catch (err) {
        console.error('Failed to export:', err);
        alert('Failed to export flowchart.');
    }
};
</script>

<template>
    <Head :title="form.title" />

    <div class="h-screen flex flex-col bg-[#f8f9fa] font-sans overflow-hidden">
        <!-- Toolbar -->
        <header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-8 z-50">
            <div class="flex items-center space-x-6">
                <Link :href="route('dashboard')" class="flex items-center space-x-3 group">
                    <div class="w-8 h-8 bg-black flex items-center justify-center group-hover:bg-gray-800 transition-colors">
                        <span class="text-white font-bold text-lg">FC</span>
                    </div>
                </Link>
                <div class="h-8 w-[1px] bg-gray-200"></div>
                <div class="flex flex-col">
                    <input 
                        v-model="form.title"
                        class="text-lg font-bold uppercase tracking-tighter border-none p-0 focus:ring-0 w-64 bg-transparent"
                        placeholder="Project Title"
                    />
                    <input 
                        v-model="form.description"
                        class="text-[10px] text-gray-400 font-bold uppercase tracking-widest border-none p-0 focus:ring-0 w-64 bg-transparent"
                        placeholder="Add description..."
                    />
                </div>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Export Dropdown -->
                <div class="relative group">
                    <button class="px-4 py-2 bg-gray-100 text-black text-xs font-bold uppercase tracking-widest hover:bg-gray-200 transition-all duration-300 flex items-center space-x-2 border border-gray-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        <span>Export</span>
                    </button>
                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 mt-0 w-32 bg-white border border-gray-200 shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 flex flex-col">
                        <button @click="exportFlowchart('png')" class="text-left px-4 py-2 text-xs font-bold text-gray-700 hover:bg-black hover:text-white transition-colors uppercase tracking-widest">
                            PNG Image
                        </button>
                        <button @click="exportFlowchart('jpg')" class="text-left px-4 py-2 text-xs font-bold text-gray-700 hover:bg-black hover:text-white transition-colors uppercase tracking-widest border-t border-gray-100">
                            JPG Image
                        </button>
                        <button @click="exportFlowchart('pdf')" class="text-left px-4 py-2 text-xs font-bold text-gray-700 hover:bg-black hover:text-white transition-colors uppercase tracking-widest border-t border-gray-100">
                            PDF Document
                        </button>
                    </div>
                </div>

                <button 
                    @click="saveProject"
                    :disabled="form.processing"
                    class="px-6 py-2 bg-black text-white text-xs font-bold uppercase tracking-widest hover:bg-gray-800 transition-all duration-300 disabled:opacity-50 flex items-center space-x-2"
                >
                    <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7.707 10.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 11.586V4a1 1 0 10-2 0v7.586l-1.293-1.293z" />
                        <path d="M5 17a2 2 0 012-2h6a2 2 0 012 2v1h2v-1a4 4 0 00-4-4H7a4 4 0 00-4 4v1h2v-1z" />
                    </svg>
                    <svg v-else class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Save Blueprint</span>
                </button>
            </div>
        </header>

        <div class="flex-1 flex overflow-hidden">
            <!-- Left Sidebar (Palette) -->
            <aside class="w-20 bg-white border-r border-gray-200 flex flex-col items-center py-8 space-y-8 z-40">
                <button @click="addBox" class="sidebar-tool group" title="Add Box">
                    <div class="w-10 h-10 border-2 border-black flex items-center justify-center group-hover:bg-black group-hover:text-white transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <span class="sidebar-label">Box</span>
                </button>

                <button @click="addText" class="sidebar-tool group" title="Add Text">
                    <div class="w-10 h-10 border-2 border-black flex items-center justify-center group-hover:bg-black group-hover:text-white transition-colors font-bold text-lg text-black">
                        T
                    </div>
                    <span class="sidebar-label">Text</span>
                </button>
            </aside>

            <!-- Editor Area -->
            <div class="flex-1 relative">
                <VueFlow
                    :nodes="nodes"
                    :edges="edges"
                    @nodes-change="onNodesChange"
                    @edges-change="onEdgesChange"
                    @edge-update="onEdgeUpdate"
                    @connect="onConnect"
                    :edges-updatable="true"
                    :connection-mode="ConnectionMode.Loose"
                    :default-edge-options="{ type: 'smoothstep', markerEnd: { type: MarkerType.ArrowClosed, color: '#000' }, style: { stroke: '#000', strokeWidth: 3, fill: 'none' }, updatable: true }"
                    class="flowchart-flow w-full h-full"
                >
                    <!-- Custom Flowchart Node -->
                    <template #node-flowchart="{ id, label, selected }">
                        <div class="flowchart-node group cursor-grab active:cursor-grabbing" :class="{ 'selected': selected }">
                            <!-- Unified Handles -->
                            <Handle id="top" type="source" :position="Position.Top" />
                            <Handle id="bottom" type="source" :position="Position.Bottom" />
                            <Handle id="left" type="source" :position="Position.Left" />
                            <Handle id="right" type="source" :position="Position.Right" />

                            <!-- Quick Add Buttons (Moved further out) -->
                            <button @click.stop="quickAdd(id, 'top')" class="quick-add-btn top-btn"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg></button>
                            <button @click.stop="quickAdd(id, 'bottom')" class="quick-add-btn bottom-btn"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg></button>
                            <button @click.stop="quickAdd(id, 'left')" class="quick-add-btn left-btn"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg></button>
                            <button @click.stop="quickAdd(id, 'right')" class="quick-add-btn right-btn"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" /></svg></button>

                            <div class="absolute inset-0 z-0"></div>
                            <textarea :value="label" @input="(e) => { updateNodeLabel(id, e.target.value); e.target.style.height = 'auto'; e.target.style.height = e.target.scrollHeight + 'px'; }" class="node-input relative z-10 nodrag" rows="1" placeholder="TYPE HERE..."></textarea>
                            <button @click.stop="removeNode(id)" class="absolute -top-3 -right-3 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-20"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></button>
                        </div>
                    </template>

                    <!-- Custom Text Node -->
                    <template #node-flowchart-text="{ id, label, selected }">
                        <div class="flowchart-text-node group cursor-grab active:cursor-grabbing" :class="{ 'selected': selected }">
                            <Handle id="top" type="source" :position="Position.Top" />
                            <Handle id="bottom" type="source" :position="Position.Bottom" />
                            <div class="absolute inset-0 z-0"></div>
                            <textarea :value="label" @input="(e) => { updateNodeLabel(id, e.target.value); e.target.style.height = 'auto'; e.target.style.height = e.target.scrollHeight + 'px'; }" class="node-input relative z-10 nodrag" rows="1" placeholder="TEXT..."></textarea>
                            <button @click.stop="removeNode(id)" class="absolute -top-3 -right-3 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow-lg z-20"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg></button>
                        </div>
                    </template>

                    <Background pattern-color="#e5e7eb" :gap="20" size="1" />
                    <Controls />
                </VueFlow>
            </div>
        </div>

        <!-- Footer Stats -->
        <footer class="h-8 bg-black flex items-center justify-between px-8 text-[9px] text-white/40 font-mono uppercase tracking-[0.2em]">
            <div class="flex space-x-8">
                <div>Nodes: {{ nodes.length }}</div>
                <div>Edges: {{ edges.length }}</div>
            </div>
            <div>Ref: ARC-FLOW-v2.0</div>
        </footer>
    </div>
</template>

<style>
/* Sidebar Styling */
.sidebar-tool {
    display: flex;
    flex-direction: column;
    align-items: center;
    cursor: pointer;
}

.sidebar-label {
    font-size: 8px;
    font-weight: 900;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-top: 4px;
    color: #9ca3af;
}

.sidebar-tool:hover .sidebar-label { color: #000; }

/* Node Styling */
.flowchart-flow .vue-flow__node-flowchart, 
.flowchart-flow .vue-flow__node-flowchart-text {
    padding: 0 !important;
    border: none !important;
    background: transparent !important;
}

.flowchart-node {
    background: #fff;
    border: 2px solid #000;
    width: 200px;
    min-height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 15px;
    position: relative;
}

.flowchart-text-node {
    background: transparent;
    border: 1px dashed transparent;
    width: 150px;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 10px;
    position: relative;
}

.flowchart-node.selected, .flowchart-text-node.selected {
    box-shadow: 0 0 0 4px rgba(0,0,0,0.1);
    border-color: #000;
    border-style: dashed;
}

/* Quick Add Buttons */
.quick-add-btn {
    position: absolute;
    width: 24px;
    height: 24px;
    background: #000;
    color: #fff;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: all 0.2s ease;
    z-index: 40;
    border: 2px solid #fff;
}

.flowchart-node:hover .quick-add-btn { opacity: 0.4; }
.quick-add-btn:hover { opacity: 1 !important; transform: scale(1.2); }

.top-btn { top: -45px; left: 50%; transform: translateX(-50%); }
.bottom-btn { bottom: -45px; left: 50%; transform: translateX(-50%); }
.left-btn { left: -45px; top: 50%; transform: translateY(-50%); }
.right-btn { right: -45px; top: 50%; transform: translateY(-50%); }

.node-input {
    width: 100%;
    border: none;
    background: transparent;
    text-align: center;
    font-size: 12px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: #000;
    resize: none;
    overflow: hidden;
    padding: 0;
    margin: 0;
}

.node-input:focus { outline: none; }

.flowchart-flow .vue-flow__edge-path { stroke: #000 !important; stroke-width: 3px !important; cursor: pointer; fill: none !important; }
.flowchart-flow .vue-flow__edge-path:hover { stroke-width: 4px !important; stroke: #ef4444 !important; }
.flowchart-flow .vue-flow__edge.selected .vue-flow__edge-path { stroke: #ef4444 !important; stroke-width: 4px !important; }

.flowchart-flow .vue-flow__handle { 
    width: 12px !important; 
    height: 12px !important; 
    background: #000 !important; 
    border: 2px solid #fff !important; 
    border-radius: 0px !important;
    opacity: 0;
    transition: opacity 0.2s ease;
    z-index: 50 !important;
}

.flowchart-node:hover .vue-flow__handle, .flowchart-text-node:hover .vue-flow__handle { 
    opacity: 1; 
}

.flowchart-flow .vue-flow__controls { border-radius: 0px; border: 1px solid #e5e7eb; box-shadow: none; }
</style>
