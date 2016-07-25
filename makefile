version = 0_0_00
outfile = Oxid_nl2go_$(version).zip

$(version): $(outfile)

$(outfile):
	zip -r  build.zip ./nl2go/*
	mv build.zip $(outfile)


clean:
	rm -rf tmp
