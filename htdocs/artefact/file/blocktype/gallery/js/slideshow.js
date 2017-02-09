function Slideshow(id, count) {
    var self = this;
    this.id = '#slideshow' + id;
    this.count = count - 1;
    this.current = 0;
    this.change = function(to) {
        if (to == this.current || to < 0 || to > this.count) {
            return false;
        }
        if ($j("#description_" + id + "_" + this.current)) {
            $j("#description_" + id + "_" + this.current).css('display', 'none');
        }
        // <EKAMPUS
        // The controls are now above the image, change height accordingly.
        var heightdiff = $j(this.id + " table").height() - $j(this.id + " img:eq(" + this.current + ")").height();
        $j(this.id).height($j(this.id + " table").height() + 15);
        // EKAMPUS>
        
        $j(this.id + " img:eq(" + this.current + ")").fadeOut(500, function() {
            var extraheight = 0;
            self.current = to;
            if ($j("#description_" + id + "_" + self.current)) {
                $j("#description_" + id + "_" + self.current).css('display','block');
                extraheight = $j("#description_" + id + "_" + self.current).height();
            }
            // <EKAMPUS
            // The controls are now above the image, change height accordingly.
            $j(self.id).height($j(self.id + " img:eq(" + self.current + ")").height() + extraheight + heightdiff + 15);
            // EKAMPUS
            $j(self.id + " img:eq(" + self.current + ")").fadeIn(500);
        });
        $j(this.id + ' td.control span').removeClass('disabled');
        if (to == 0) {
            $j(this.id + ' td.control span.prev').addClass('disabled');
            $j(this.id + ' td.control span.first').addClass('disabled');
        }
        else if (to == this.count) {
            $j(this.id + ' td.control span.next').addClass('disabled');
            $j(this.id + ' td.control span.last').addClass('disabled');
        }
        return false;
    }
    $j(this.id + ' td.control span.next').click(function() {return self.change(self.current + 1);});
    $j(this.id + ' td.control span.prev').click(function() {return self.change(self.current - 1);});
    $j(this.id + ' td.control span.first').click(function() {return self.change(0);});
    $j(this.id + ' td.control span.last').click(function() {return self.change(self.count);});
    $j(this.id + " img").hide();
    $j(this.id + " img:eq(" + this.current + ")").show();
    if (this.current < this.count) {
        $j(this.id + ' td.control span.next').removeClass('disabled');
        $j(this.id + ' td.control span.last').removeClass('disabled');
    }
}

