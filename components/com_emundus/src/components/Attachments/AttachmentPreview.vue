<template>
	<div id="em-attachment-preview">
		<div
			ref="a-preview"
			class="attachment-preview"
			:class="{
        'overflow-x': overflowX,
        'overflow-y': overflowY,
        'hidden': !needShadowDOM
      }"
		></div>
    <div v-if="!needShadowDOM" v-html="preview" class="attachment-preview"></div>
		<div id="msg" :class="{ active: msg && openMsg }">
			<p>{{ msg }}</p>
		</div>
	</div>
</template>

<script>
import attachmentService from "../../services/attachment";

export default {
	props: {
		user: {
			type: Number,
			required: true,
		},
    defaultAttachment: {
      type: Object,
      default: null,
    }
	},
	data() {
		return {
			attachment: null,
			preview: "",
			overflowX: false,
			overflowY: false,
			style: "",
			msg: "",
			openMsg: false,
      needShadowDOM: false,
		};
	},
  mounted() {
		if (typeof this.$refs["a-preview"].attachShadow === 'function') {
			this.$refs["a-preview"].attachShadow({ mode: "open" });
		}
    this.attachment = this.defaultAttachment;
		this.getPreview();
	},
	methods: {
		async getPreview() {
			let data;
			if (!this.$store.state.attachment.previews[this.attachment.aid]) {
				data = await attachmentService.getPreview(
					this.user,
					this.attachment.filename,
            this.attachment.aid
				);

				// store preview data
				if (data.status) {
					this.$store.commit("attachment/setPreview", {
						preview: data,
						id: this.attachment.aid,
					});
				}
			} else {
				data = this.$store.state.attachment.previews[this.attachment.aid];
			}

			this.$emit("canDownload");
			if (data.status) {
				this.preview = data.content;
				this.overflowX = data.overflowX;
				this.overflowY = data.overflowY;
				this.style = data.style;

				if (data.msg) {
					this.msg = data.msg;
					this.openMsg = true;

					setTimeout(() => {
						this.openMsg = false;
					}, 3000);
				}
			} else {
				if (data.error === "file_not_found") {
					this.$emit("fileNotFound");
				}

				this.overflowX = false;
				this.overflowY = false;
				this.preview = data.content;
				this.msg = "";
			}

      switch(this.style) {
        case "sheet":
        case "presentation":
        case "word":
          this.needShadowDOM = true;
          break;
        default:
          this.needShadowDOM = false;
          break;
      }

      if (this.needShadowDOM) {
        this.$refs["a-preview"].shadowRoot.innerHTML = this.preview != null ? this.preview : "";

        if (this.style == "sheet") {
          this.addSheetStyles();
        } else if (this.style == "presentation") {
          this.addPresentationStyles();
        } else if (this.style == "word") {
          this.addWordStyles();
        }
      } else {
        this.$refs["a-preview"].shadowRoot.innerHTML = "";
      }
		},
		addSheetStyles() {
			// get div elements of first level
			const pages = this.$refs["a-preview"].shadowRoot.querySelectorAll("div");
			pages.forEach((div, key) => {
				div.style.width = "fit-content";
				div.style.margin = "20px auto";
				div.style.boxShadow = "0px 0px 10px rgba(0, 0, 0, 0.1)";

				if (key > 0) {
					div.style.display = "none";
				}
			});

			const navigation =
				this.$refs["a-preview"].shadowRoot.querySelector(".navigation");
			if (navigation) {
				navigation.style.display = "flex";
				navigation.style.flexDirection = "row";
				navigation.style.justifyContent = "flex-start";
				navigation.style.alignItems = "center";

				navigation.querySelectorAll("li").forEach((li, li_key) => {
					li.style.listStyleType = "none";
					li.style.margin = "0 10px";

					li.addEventListener("click", () => {
						pages.forEach((div, div_key) => {
							if (div_key == li_key) {
								div.style.display = "block";
							} else {
								div.style.display = "none";
							}
						});
					});
				});
			}
		},
		addPresentationStyles() {
			const slides =
				this.$refs["a-preview"].shadowRoot.querySelectorAll(".slide");
			slides.forEach((slide) => {
				slide.style.padding = "16px";
				slide.style.margin = "20px";
				slide.style.width = "calc(100% - 72px)";
				slide.style.boxShadow = "0px 0px 10px rgba(0, 0, 0, 0.1)";
				slide.style.borderRadius = "8px";
				slide.style.backgroundColor = "white";
			});
		},
		addWordStyles() {
			const wrapper =
				this.$refs["a-preview"].shadowRoot.querySelector(".wrapper");

			if (wrapper) {
				wrapper.style.border = "var(--border-color)";
				wrapper.style.boxShadow = "var(--box-shadow)";
				wrapper.style.backgroundColor = "white";
				wrapper.style.padding = "20px";
				wrapper.style.margin = "16px";
				wrapper.style.overflow = "hidden";
			}
		},
	}
};
</script>

<style lang="scss">
#em-attachment-preview {
	height: 100%;
	overflow: hidden;
	position: relative;

	.attachment-preview {
		height: 100%;
		width: 100%;
		overflow: hidden;
		background-color: var(--grey-bg-color);

		&.overflow-x {
			overflow-x: auto;
		}

		&.overflow-y {
			overflow-y: auto;
		}
	}

	#msg {
		position: absolute;
		top: 20px;
		left: 8px;
		padding: 16px;
		width: calc(100% - 26px);
		background-color: var(--warning-bg-color);
		color: var(--warning-color);
		opacity: 0;
		z-index: -1;
		transition: all 0.3s;

		&.active {
			opacity: 1;
			z-index: 2;
		}
	}
}
</style>
