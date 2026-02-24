import { Renderer, Program, Mesh, Triangle } from "ogl";
import gsap from "gsap";

// Helper: Hex to RGB Array [0-1, 0-1, 0-1]
const hexToRgb = (hex) => {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result
        ? [
              parseInt(result[1], 16) / 255,
              parseInt(result[2], 16) / 255,
              parseInt(result[3], 16) / 255,
          ]
        : [1, 1, 1];
};

const vertex = `#version 300 es
in vec2 position;
void main() {
    gl_Position = vec4(position, 0.0, 1.0);
}`;

const fragment = `#version 300 es
precision highp float;
uniform vec2 iResolution;
uniform float iTime;
uniform float uTimeSpeed;
uniform float uColorBalance;
uniform float uWarpStrength;
uniform float uWarpFrequency;
uniform float uWarpSpeed;
uniform float uWarpAmplitude;
uniform float uBlendAngle;
uniform float uBlendSoftness;
uniform float uRotationAmount;
uniform float uNoiseScale;
uniform float uGrainAmount;
uniform float uGrainScale;
uniform float uGrainAnimated;
uniform float uContrast;
uniform float uGamma;
uniform float uSaturation;
uniform vec2 uCenterOffset;
uniform float uZoom;
uniform vec3 uColor1;
uniform vec3 uColor2;
uniform vec3 uColor3;
out vec4 fragColor;
#define S(a,b,t) smoothstep(a,b,t)
mat2 Rot(float a){float s=sin(a),c=cos(a);return mat2(c,-s,s,c);} 
vec2 hash(vec2 p){p=vec2(dot(p,vec2(2127.1,81.17)),dot(p,vec2(1269.5,283.37)));return fract(sin(p)*43758.5453);} 
float noise(vec2 p){vec2 i=floor(p),f=fract(p),u=f*f*(3.0-2.0*f);float n=mix(mix(dot(-1.0+2.0*hash(i+vec2(0.0,0.0)),f-vec2(0.0,0.0)),dot(-1.0+2.0*hash(i+vec2(1.0,0.0)),f-vec2(1.0,0.0)),u.x),mix(dot(-1.0+2.0*hash(i+vec2(0.0,1.0)),f-vec2(0.0,1.0)),dot(-1.0+2.0*hash(i+vec2(1.0,1.0)),f-vec2(1.0,1.0)),u.x),u.y);return 0.5+0.5*n;}
void mainImage(out vec4 o, vec2 C){
    float t=iTime*uTimeSpeed;
    vec2 uv=C/iResolution.xy;
    float ratio=iResolution.x/iResolution.y;
    vec2 tuv=uv-0.5+uCenterOffset;
    tuv/=max(uZoom,0.001);

    float degree=noise(vec2(t*0.1,tuv.x*tuv.y)*uNoiseScale);
    tuv.y*=1.0/ratio;
    tuv*=Rot(radians((degree-0.5)*uRotationAmount+180.0));
    tuv.y*=ratio;

    float frequency=uWarpFrequency;
    float ws=max(uWarpStrength,0.001);
    float amplitude=uWarpAmplitude/ws;
    float warpTime=t*uWarpSpeed;
    tuv.x+=sin(tuv.y*frequency+warpTime)/amplitude;
    tuv.y+=sin(tuv.x*(frequency*1.5)+warpTime)/(amplitude*0.5);

    vec3 colLav=uColor1;
    vec3 colOrg=uColor2;
    vec3 colDark=uColor3;
    float b=uColorBalance;
    float s=max(uBlendSoftness,0.0);
    mat2 blendRot=Rot(radians(uBlendAngle));
    float blendX=(tuv*blendRot).x;
    float edge0=-0.3-b-s;
    float edge1=0.2-b+s;
    float v0=0.5-b+s;
    float v1=-0.3-b-s;
    vec3 layer1=mix(colDark,colOrg,S(edge0,edge1,blendX));
    vec3 layer2=mix(colOrg,colLav,S(edge0,edge1,blendX));
    vec3 col=mix(layer1,layer2,S(v0,v1,tuv.y));

    vec2 grainUv=uv*max(uGrainScale,0.001);
    if(uGrainAnimated>0.5){grainUv+=vec2(iTime*0.05);} 
    float grain=fract(sin(dot(grainUv,vec2(12.9898,78.233)))*43758.5453);
    col+=(grain-0.5)*uGrainAmount;

    col=(col-0.5)*uContrast+0.5;
    float luma=dot(col,vec3(0.2126,0.7152,0.0722));
    col=mix(vec3(luma),col,uSaturation);
    col=pow(max(col,0.0),vec3(1.0/max(uGamma,0.001)));
    col=clamp(col,0.0,1.0);

    o=vec4(col,1.0);
}
void main(){
    vec4 o=vec4(0.0);
    mainImage(o,gl_FragCoord.xy);
    fragColor=o;
}`;

export default (config = {}) => ({
    // State matches shader uniforms
    params: {
        timeSpeed: config.timeSpeed ?? 0.25,
        colorBalance: config.colorBalance ?? 0.0,
        warpStrength: config.warpStrength ?? 1.0,
        warpFrequency: config.warpFrequency ?? 5.0,
        warpSpeed: config.warpSpeed ?? 2.0,
        warpAmplitude: config.warpAmplitude ?? 50.0,
        blendAngle: config.blendAngle ?? 0.0,
        blendSoftness: config.blendSoftness ?? 0.2,
        rotationAmount: config.rotationAmount ?? 500.0,
        noiseScale: config.noiseScale ?? 2.0,
        grainAmount: config.grainAmount ?? 0.04,
        grainScale: config.grainScale ?? 1.5,
        grainAnimated: config.grainAnimated ?? false,
        contrast: config.contrast ?? 1.0,
        gamma: config.gamma ?? 1.0,
        saturation: config.saturation ?? 1.3,
        centerX: config.centerX ?? 0.0,
        centerY: config.centerY ?? 0.0,
        zoom: config.zoom ?? 1.0,
        color1: config.color1 ?? "#1e40af",
        color2: config.color2 ?? "#2563eb",
        color3: config.color3 ?? "#1e3a8a",
    },

    renderer: null,
    program: null,
    raf: null,
    resizeObserver: null,

    init() {
        // 1. Setup OGL Renderer
        this.renderer = new Renderer({
            dpr: Math.min(window.devicePixelRatio || 1, 2),
            alpha: true,
            antialias: false,
            webgl: 2,
        });

        const gl = this.renderer.gl;
        const container = this.$refs.container;

        // 2. Style & Append Canvas
        gl.canvas.style.width = "100%";
        gl.canvas.style.height = "100%";
        gl.canvas.style.display = "block";
        gl.canvas.style.position = "absolute";
        gl.canvas.style.top = "0";
        gl.canvas.style.left = "0";
        container.appendChild(gl.canvas);

        // 3. Define Geometry & Program
        const geometry = new Triangle(gl);

        this.program = new Program(gl, {
            vertex,
            fragment,
            uniforms: {
                iTime: { value: 0 },
                iResolution: {
                    value: new Float32Array([
                        gl.canvas.width,
                        gl.canvas.height,
                    ]),
                },
                uTimeSpeed: { value: this.params.timeSpeed },
                uColorBalance: { value: this.params.colorBalance },
                uWarpStrength: { value: this.params.warpStrength },
                uWarpFrequency: { value: this.params.warpFrequency },
                uWarpSpeed: { value: this.params.warpSpeed },
                uWarpAmplitude: { value: this.params.warpAmplitude },
                uBlendAngle: { value: this.params.blendAngle },
                uBlendSoftness: { value: this.params.blendSoftness },
                uRotationAmount: { value: this.params.rotationAmount },
                uNoiseScale: { value: this.params.noiseScale },
                uGrainAmount: { value: this.params.grainAmount },
                uGrainScale: { value: this.params.grainScale },
                uGrainAnimated: {
                    value: this.params.grainAnimated ? 1.0 : 0.0,
                },
                uContrast: { value: this.params.contrast },
                uGamma: { value: this.params.gamma },
                uSaturation: { value: this.params.saturation },
                uCenterOffset: {
                    value: new Float32Array([
                        this.params.centerX,
                        this.params.centerY,
                    ]),
                },
                uZoom: { value: this.params.zoom },
                uColor1: {
                    value: new Float32Array(hexToRgb(this.params.color1)),
                },
                uColor2: {
                    value: new Float32Array(hexToRgb(this.params.color2)),
                },
                uColor3: {
                    value: new Float32Array(hexToRgb(this.params.color3)),
                },
            },
        });

        const mesh = new Mesh(gl, { geometry, program: this.program });

        // 4. Resize Handling
        const setSize = () => {
            const rect = container.getBoundingClientRect();
            this.renderer.setSize(rect.width, rect.height);
            this.program.uniforms.iResolution.value[0] = gl.drawingBufferWidth;
            this.program.uniforms.iResolution.value[1] = gl.drawingBufferHeight;
        };

        this.resizeObserver = new ResizeObserver(setSize);
        this.resizeObserver.observe(container);
        setSize();

        // 5. Setup Watchers to update uniforms when Alpine data changes
        this.$watch("params", (val) => this.updateUniforms(), {
            deep: true,
        });

        // 6. Animation Loop
        let t0 = performance.now();
        const loop = (t) => {
            if (!this.program) return;

            this.program.uniforms.iTime.value = (t - t0) * 0.001;
            this.renderer.render({ scene: mesh });
            this.raf = requestAnimationFrame(loop);
        };
        this.raf = requestAnimationFrame(loop);
    },

    // Helper to efficiently map state to WebGL uniforms
    updateUniforms() {
        if (!this.program) return;
        const u = this.program.uniforms;
        const p = this.params;

        // Float updates
        u.uTimeSpeed.value = p.timeSpeed;
        u.uColorBalance.value = p.colorBalance;
        u.uWarpStrength.value = p.warpStrength;
        u.uWarpFrequency.value = p.warpFrequency;
        u.uWarpSpeed.value = p.warpSpeed;
        u.uWarpAmplitude.value = p.warpAmplitude;
        u.uBlendAngle.value = p.blendAngle;
        u.uBlendSoftness.value = p.blendSoftness;
        u.uRotationAmount.value = p.rotationAmount;
        u.uNoiseScale.value = p.noiseScale;
        u.uGrainAmount.value = p.grainAmount;
        u.uGrainScale.value = p.grainScale;
        u.uGrainAnimated.value = p.grainAnimated ? 1.0 : 0.0;
        u.uContrast.value = p.contrast;
        u.uGamma.value = p.gamma;
        u.uSaturation.value = p.saturation;
        u.uZoom.value = p.zoom;

        // Vector updates
        u.uCenterOffset.value.set([p.centerX, p.centerY]);
        u.uColor1.value.set(hexToRgb(p.color1));
        u.uColor2.value.set(hexToRgb(p.color2));
        u.uColor3.value.set(hexToRgb(p.color3));
    },

    // GSAP Interface: Call this to animate values smoothly
    tween(props, duration = 1) {
        gsap.to(this.params, {
            ...props,
            duration: duration,
            ease: "power2.out",
            // Alpine watchers will catch the changes and update WebGL automatically
        });
    },

    destroy() {
        if (this.raf) cancelAnimationFrame(this.raf);
        if (this.resizeObserver) this.resizeObserver.disconnect();

        // Optional: Remove canvas
        if (this.$refs.container && this.renderer) {
            this.$refs.container.removeChild(this.renderer.gl.canvas);
        }
    },
});
